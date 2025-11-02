<?php

namespace Drupal\logo_image_enhanced\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\image\Entity\ImageStyle;
use Drupal\Core\File\FileSystemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Image\ImageFactory;

/**
 * Modifica la respuesta HTML para actualizar los atributos del logo.
 */
class LogoAttributesEventSubscriber implements EventSubscriberInterface {

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, FileSystemInterface $file_system) {
    $this->configFactory = $config_factory;
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('file_system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    // Alta prioridad para ejecutarse tarde en el ciclo de renderizado.
    $events[KernelEvents::RESPONSE][] = ['onResponse', -100];
    return $events;
  }

  /**
   * Reacts to the response event.
   *
   * @param \Symfony\Component\HttpKernel\Event\ResponseEvent $event
   *   The response event.
   */
  public function onResponse(ResponseEvent $event) {
    // Solo procesar respuestas HTML completas.
    $response = $event->getResponse();
    if (!$response || !method_exists($response, 'getContent')) {
      return;
    }

    $content = $response->getContent();
    if (empty($content)) {
      return;
    }

    // Obtener la configuración
    $config = $this->configFactory->get('logo_image_enhanced.settings');
    $site_name = $this->configFactory->get('system.site')->get('name');
    $logo_alt = $config->get('logo_alt') ?: $site_name;
    $logo_title = $config->get('logo_title') ?: $site_name;
    $logo_style = $config->get('logo_style');
    $logo_path = $config->get('logo_path');

    // Buscar dinámicamente el bloque de branding del sitio
    // Patrones comunes para bloques de branding en diferentes temas
    $branding_patterns = [
      // Patrones generales para bloques de branding
      '/<div[^>]*id="[^"]*site-branding[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<div[^>]*class="[^"]*site-branding[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<div[^>]*class="[^"]*navbar-brand[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<a[^>]*class="[^"]*logo[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<a[^>]*class="[^"]*site-logo[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<div[^>]*class="[^"]*logo[^"]*"[^>]*>.*?<img[^>]*>/s',
      '/<div[^>]*id="logo"[^>]*>.*?<img[^>]*>/s',
      // Patrones específicos (incluido el original de tu tema)
      '/<div id="block-domhostseo-site-branding".*?<img[^>]*>/s',
      '/<div[^>]*id="block-[^-]+-site-branding"[^>]*>.*?<img[^>]*>/s',
      '/<div[^>]*id="block-[^-]+-branding"[^>]*>.*?<img[^>]*>/s',
    ];
    
    $logo_found = false;
    $old_img = '';
    
    // Intentar con cada patrón hasta encontrar una coincidencia
    foreach ($branding_patterns as $pattern) {
      if (preg_match($pattern, $content, $matches)) {
        // Extraer la etiqueta img
        $img_pattern = '/<img[^>]*>/';
        if (preg_match($img_pattern, $matches[0], $img_matches)) {
          $old_img = $img_matches[0];
          $logo_found = true;
          
          \Drupal::logger('logo_image_enhanced')->notice('Logo encontrado con patrón: @pattern', [
            '@pattern' => $pattern,
          ]);
          
          break;
        }
      }
    }
    
    // Si no se encontró un logo con los patrones predefinidos, buscar cualquier imagen que parezca un logo
    if (!$logo_found) {
      // Buscar imágenes con clases o IDs de logo comunes
      $logo_img_patterns = [
        '/<img[^>]*class="[^"]*logo[^"]*"[^>]*>/s',
        '/<img[^>]*id="[^"]*logo[^"]*"[^>]*>/s',
        '/<img[^>]*alt="[^"]*logo[^"]*"[^>]*>/s',
        '/<img[^>]*alt="[^"]*' . preg_quote($site_name, '/') . '[^"]*"[^>]*>/s',
      ];
      
      foreach ($logo_img_patterns as $pattern) {
        if (preg_match($pattern, $content, $matches)) {
          $old_img = $matches[0];
          $logo_found = true;
          
          \Drupal::logger('logo_image_enhanced')->notice('Logo detectado por heurística: @pattern', [
            '@pattern' => $pattern,
          ]);
          
          break;
        }
      }
    }

    // Proceder solo si se encontró un logo
    if ($logo_found && !empty($old_img)) {
      // Crear nueva img con los atributos actualizados
      $new_img = preg_replace('/alt="[^"]*"/', 'alt="' . htmlspecialchars($logo_alt) . '"', $old_img);
      $new_img = preg_replace('/title="[^"]*"/', 'title="' . htmlspecialchars($logo_title) . '"', $new_img);
      
      // Si no existe el atributo alt, añadirlo
      if (strpos($new_img, 'alt="') === false) {
        $new_img = str_replace('<img ', '<img alt="' . htmlspecialchars($logo_alt) . '" ', $new_img);
      }
      
      // Si no existe el atributo title, añadirlo
      if (strpos($new_img, 'title="') === false) {
        $new_img = str_replace('<img ', '<img title="' . htmlspecialchars($logo_title) . '" ', $new_img);
      }
      
      // Aplicar estilo de imagen si está configurado
      if (!empty($logo_style) && $logo_style !== '_none' && !empty($logo_path)) {
        // Extraer la URL original de la imagen
        if (preg_match('/src="([^"]*)"/', $old_img, $src_matches)) {
          // Aplicar el estilo de imagen
          $style = ImageStyle::load($logo_style);
          if ($style) {
            $uri = (strpos($logo_path, 'public://') === 0) ? $logo_path : 'public://' . ltrim($logo_path, '/');
            
            // Verificar si el archivo existe
            if (file_exists($uri)) {
              $url = $style->buildUrl($uri);
              
              // Obtener las operaciones del estilo de imagen
              $effects = $style->getEffects();
              $width = null;
              $height = null;

              // Iterar por los efectos para encontrar dimensiones
              foreach ($effects as $effect) {
                $configuration = $effect->getConfiguration();
                
                // Buscar efectos de redimensionamiento
                if (
                  $configuration['id'] === 'image_resize' || 
                  $configuration['id'] === 'image_scale' || 
                  $configuration['id'] === 'image_scale_and_crop'
                ) {
                  $width = $configuration['data']['width'] ?? $width;
                  $height = $configuration['data']['height'] ?? $height;
                }
              }

              // Si no se encontraron dimensiones, usar dimensiones originales
              if ($width === null || $height === null) {
                $image_info = \Drupal::service('image.factory')->get($uri);
                $width = $width ?? $image_info->getWidth();
                $height = $height ?? $image_info->getHeight();
              }
              
              // Reemplazar la URL en la etiqueta img
              $new_img = preg_replace('/src="[^"]*"/', 'src="' . $url . '"', $new_img);
              
              // Agregar width y height
              if (!empty($width) && !empty($height)) {
                $new_img = preg_replace('/\s*\/>/', ' width="' . $width . '" height="' . $height . '" />', $new_img);
              }
              
              // Registrar acción
              \Drupal::logger('logo_image_enhanced')->notice('Estilo de imagen aplicado: @style, URL: @url, Ancho: @width, Alto: @height', [
                '@style' => $logo_style,
                '@url' => $url,
                '@width' => $width ?? 'N/A',
                '@height' => $height ?? 'N/A',
              ]);
            }
            else {
              \Drupal::logger('logo_image_enhanced')->warning('El archivo del logo no existe: @uri', [
                '@uri' => $uri,
              ]);
            }
          }
        }
      }
      
      // Reemplazar en el contenido
      $content = str_replace($old_img, $new_img, $content);
      $response->setContent($content);
      
      // Registrar acción
      \Drupal::logger('logo_image_enhanced')->notice('Atributos del logo actualizados. Alt: @alt, Title: @title, Estilo: @style', [
        '@alt' => $logo_alt,
        '@title' => $logo_title,
        '@style' => $logo_style,
      ]);
    } else {
      \Drupal::logger('logo_image_enhanced')->warning('No se pudo encontrar el logo en el HTML. No se aplicaron cambios.');
    }
  }
}