<?php

/**
 * Archivo de diagnóstico temporal para logo_image_enhanced.
 */

use Drupal\Core\Url;

/**
 * Implements hook_page_bottom().
 */
function logo_image_enhanced_page_bottom(array &$page_bottom) {
  // Solo mostrar diagnóstico para administradores
  if (!\Drupal::currentUser()->hasPermission('administer site configuration')) {
    return;
  }

  // Configuración actual
  $config = \Drupal::config('logo_image_enhanced.settings');
  $logo_alt = $config->get('logo_alt');
  $logo_title = $config->get('logo_title');
  $logo_style = $config->get('logo_style');
  $logo_path = $config->get('logo_path');

  // Crear un bloque de diagnóstico detallado
  $diagnostic = [
    '#type' => 'details',
    '#title' => t('Logo Image Style - Diagnóstico Detallado'),
    '#open' => TRUE,
    '#attributes' => [
      'style' => 'position: fixed; bottom: 0; right: 0; z-index: 9999; background: white; padding: 10px; border: 1px solid #ccc; max-width: 600px; max-height: 400px; overflow: auto;',
    ],
    'content' => [
      '#markup' => '<div>' .
        '<h3>Configuración del Logo</h3>' .
        '<p><strong>Alt Text:</strong> ' . htmlspecialchars($logo_alt) . '</p>' .
        '<p><strong>Title Text:</strong> ' . htmlspecialchars($logo_title) . '</p>' .
        '<p><strong>Estilo de Imagen:</strong> ' . htmlspecialchars($logo_style) . '</p>' .
        '<p><strong>Ruta del Logo:</strong> ' . htmlspecialchars($logo_path) . '</p>' .
        '<hr>' .
        '<h3>Diagnóstico de Imágenes</h3>' .
        '<div id="logo-image-diagnostic"></div>' .
        '<script>
          document.addEventListener("DOMContentLoaded", function() {
            var diagnostic = document.getElementById("logo-image-diagnostic");
            var logos = document.querySelectorAll("img[class*=\'logo\'], img[id*=\'logo\']");
            
            if (logos.length === 0) {
              diagnostic.innerHTML = "<p>No se encontraron imágenes con clases o IDs relacionadas con logos.</p>";
            } else {
              var html = "<ul>";
              logos.forEach(function(logo, index) {
                html += "<li>" +
                  "Logo #" + (index + 1) + ":<br>" +
                  "Src: " + (logo.src || "N/A") + "<br>" +
                  "Alt: " + (logo.alt || "N/A") + "<br>" +
                  "Class: " + (logo.className || "N/A") + "<br>" +
                  "ID: " + (logo.id || "N/A") +
                  "</li>";
              });
              html += "</ul>";
              diagnostic.innerHTML = html;
            }
          });
        </script>' .
        '<a href="' . Url::fromRoute('system.site_information_settings')->toString() . '">Ir a configuración</a>' .
        '</div>',
    ],
  ];

  $page_bottom['logo_diagnostic'] = $diagnostic;
}