<?php

namespace Drupal\logo_image_enhanced\Plugin\Block;

use Drupal\Core\Render\Element\RenderCallbackInterface;
use Drupal\image\Entity\ImageStyle;

/**
 * Provides a trusted callback to alter the system branding block.
 *
 * @see logo_image_enhanced_block_view_system_branding_block_alter()
 */
class LogoBlockViewBuilder implements RenderCallbackInterface {

  /**
   * #pre_render callback: Applies an image style to the site logo.
   */
  public static function preRender($build) {
    // Forzar recarga de la configuración para asegurar que tenemos los valores más recientes
    \Drupal::configFactory()->reset('logo_image_enhanced.settings');
    
    // Usar nuestra propia configuración
    $config = \Drupal::config('logo_image_enhanced.settings');
    $logo_style = $config->get('logo_style') ?? '_none';
    $logo_image = $config->get('logo_path') ?? '';
    
    // Obtener los valores para alt y title desde la configuración
    $site_name = \Drupal::config('system.site')->get('name');
    $logo_alt = $config->get('logo_alt') ?? $site_name;
    $logo_title = $config->get('logo_title') ?? $site_name;
    
    // Añadir información de depuración
    \Drupal::logger('logo_image_enhanced')->notice('Aplicando estilo de imagen al logo. Estilo: @style, Alt: @alt, Title: @title, Path: @path', [
      '@style' => $logo_style,
      '@alt' => $logo_alt,
      '@title' => $logo_title,
      '@path' => $logo_image,
    ]);

    // Verificar si hay un logo en el bloque
    if (isset($build['content']['site_logo'])) {
      
      if (!empty($logo_style) && $logo_style !== '_none' && !empty($logo_image)) {
        // Usar la URI directamente si ya está en formato URI, de lo contrario convertirla
        $uri = (strpos($logo_image, 'public://') === 0) ? $logo_image : 'public://' . ltrim($logo_image, '/');
        $style = ImageStyle::load($logo_style);

        if ($style && isset($build['content']['site_logo']['#uri']) && file_exists($uri)) {
          $url = $style->buildUrl($uri);
          $build['content']['site_logo']['#uri'] = $url;
        }
      }
      
      // Manejo directo y explícito de atributos
      // En Drupal 10, los atributos pueden estar en diferentes lugares dependiendo de cómo se renderiza el logo
      
      // 1. Soporte para cuando el logo se renderiza como elemento img
      if (!isset($build['content']['site_logo']['#attributes'])) {
        $build['content']['site_logo']['#attributes'] = [];
      }
      $build['content']['site_logo']['#attributes']['alt'] = $logo_alt;
      $build['content']['site_logo']['#attributes']['title'] = $logo_title;
      
      // 2. Soporte para cuando el logo se renderiza como elemento interno de un enlace
      if (isset($build['content']['site_logo']['#items'][0])) {
        if (!isset($build['content']['site_logo']['#items'][0]['attributes'])) {
          $build['content']['site_logo']['#items'][0]['attributes'] = [];
        }
        $build['content']['site_logo']['#items'][0]['attributes']['alt'] = $logo_alt;
        $build['content']['site_logo']['#items'][0]['attributes']['title'] = $logo_title;
      }
      
      // 3. Soporte cuando el logo está dentro de un array de markup
      if (isset($build['content']['site_logo']['#markup'])) {
        // Añadir un postprocesamiento para reemplazar los atributos directamente en el HTML
        $build['content']['site_logo']['#post_render'][] = function($markup, $elements) use ($logo_alt, $logo_title) {
          // Reemplazar alt y title en el markup del logo
          $markup = preg_replace('/alt="[^"]*"/', 'alt="' . htmlspecialchars($logo_alt) . '"', $markup);
          $markup = preg_replace('/title="[^"]*"/', 'title="' . htmlspecialchars($logo_title) . '"', $markup);
          return $markup;
        };
      }
      
      // 4. Soporte para logos SVG que pueden tener estructura diferente
      if (isset($build['content']['site_logo']['#theme']) && $build['content']['site_logo']['#theme'] === 'image') {
        $build['content']['site_logo']['#alt'] = $logo_alt;
        $build['content']['site_logo']['#title'] = $logo_title;
      }
    }

    return $build;
  }
}