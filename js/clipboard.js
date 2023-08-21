/**
 * @file
 * Citation Select behaviors.
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Attach clipboard.js to button.
   */
  Drupal.behaviors.citationSelect = {
    attach: function (context, settings) {
      new ClipboardJS('.clipboard-button');
    }
  }
} (jQuery, Drupal));
