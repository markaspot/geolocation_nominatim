<?php

namespace Drupal\geolocation_nominatim\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\geolocation\Plugin\Field\FieldWidget\GeolocationLatlngWidget;

/**
 * Plugin implementation of the 'geolocation_nominatim_widget' widget.
 *
 * @FieldWidget(
 *   id = "geolocation_nominatim_widget",
 *   label = @Translation("Geolocation nominatim widget"),
 *   field_types = {
 *     "geolocation"
 *   }
 * )
 */
class GeolocationNominatimWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'zoom' => 12,
      'center_lat' => 0,
      'center_lng' => 0,
      'set_address_field' => 0,
      'limit_countrycodes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $uniq_id = Html::getUniqueId('geolocation-nominatim-map');
    $elements = [];
    for ($i = 0; $i <= 14; $i++) {
      $zoom_options[$i] = $i;
    }
    $elements['zoom'] = [
      '#type' => 'select',
      '#title' => $this->t('Zoom level'),
      '#options' => $zoom_options,
      '#default_value' => $this->getSetting('zoom'),
      '#attributes' => ['class' => ['geolocation-widget-zoom', 'for--' . $uniq_id ]],
    ];
    $elements['center_lat'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Center (Latitude)'),
      '#default_value' => $this->getSetting('center_lat'),
      '#attributes' => ['class' => ['geolocation-widget-lat', 'for--' . $uniq_id ]],
    ];
    $elements['center_lng'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Center (Longitude)'),
      '#default_value' => $this->getSetting('center_lng'),
      '#attributes' => ['class' => ['geolocation-widget-lng', 'for--' . $uniq_id ]],
    ];
    $elements['limit_countrycodes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Limit search results to one or more countries'),
      '#default_value' => $this->getSetting('limit_countrycodes'),
      '#description' => $this->t('Optionally enter a comma-seperated list 2-letter country codes to limit search results.')
    ];
    $elements['set_address_field'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Populate address field (experimental)'),
      '#default_value' => $this->getSetting('set_address_field'),
      '#description' => $this->t('Experimental feature: Populate an address field with the geocoding results. This works only if the form has one field of type address (https://www.drupal.org/project/address) and might not cover all countries and circumnstances. NOTE: The address form fields will be populated even if they already contain default values. Use with care and not yet in production.')
    ];

//    This somehow doesn't work, javascript error, seems a library isn't loaded.
//    @todo: Fix this.
//    $elements['map'] = [
//      '#type' => 'html_tag',
//      '#tag' => 'div',
//      '#attributes' => ['id' => $uniq_id, 'style' => 'width: 100%; height: 400px'],
//    ];
//    $elements['#attached'] = [
//      'library' => ['geolocation_nominatim/geolocation-nominatim-widget'],
//      'drupalSettings' => [
//        'geolocationNominatim' => [
//          'widgetMaps' => [
//            $uniq_id => [
//              'id' => $uniq_id,
//              'lat' => (float) $elements['center_lat']['#default_value'],
//              'lng' => (float) $elements['center_lng']['#default_value'],
//              'zoom' => (float) $elements['zoom']['#default_value'],
//            ],
//          ],
//        ],
//      ],
//    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $uniq_id = Html::getUniqueId('geolocation-nominatim-map');

    $element['lat'] = array(
      '#type' => 'hidden',
      '#empty_value' => '',
      '#default_value' => (isset($items[$delta]->lat)) ? $items[$delta]->lat : NULL,
      '#attributes' => ['class' => ['geolocation-widget-lat', 'for--' . $uniq_id ]],
    );

    $element['lng'] = array(
      '#type' => 'hidden',
      '#empty_value' => '',
      '#default_value' => (isset($items[$delta]->lng)) ? $items[$delta]->lng : NULL,
      '#attributes' => ['class' => ['geolocation-widget-lng', 'for--' . $uniq_id ]],
    );

    $element['map_container'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Map'),
      '#collapsible' => true,
      '#collapsed' => true,
      'map' => [
        '#type' => 'html_tag',
        '#tag' => 'div',
        '#attributes' => ['id' => $uniq_id, 'style' => 'width: 100%; height: 400px'],
      ]
    ];

    $label = '';
    if ($items->getEntity()->label()) {
      $label =  $items->getEntity()->label();
    }

    $element['#attached'] = [
      'library' => ['geolocation_nominatim/geolocation-nominatim-widget'],
      'drupalSettings' => [
        'geolocationNominatim' => [
          'widgetMaps' => [
            $uniq_id => [
              'id' => $uniq_id,
              'centerLat' => !empty($element['lat']['#default_value']) ? $element['lat']['#default_value'] : $this->getSetting('center_lat'),
              'centerLng' => !empty($element['lng']['#default_value']) ? $element['lng']['#default_value'] : $this->getSetting('center_lng'),
              'zoom' => $this->getSetting('zoom'),
              'lat' => (float) $element['lat']['#default_value'],
              'lng' => (float) $element['lng']['#default_value'],
              'label' => $label,
              'setAddressField' => $this->getSetting('set_address_field'),
              'limitCountryCodes'  => $this->getSetting('limit_countrycodes'),
            ],
          ],
        ],
      ],
    ];

    return $element;
  }

}
