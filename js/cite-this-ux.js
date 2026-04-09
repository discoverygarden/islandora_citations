/**
 * @file
 * Improves the accessibility and UX of the "Cite this" citation block.
 *
 * - Keeps the copy-to-clipboard button label in sync with the selected style.
 * - Disables the button when no style is selected.
 * - Announces citation preview changes to screen readers via a live region.
 * - Announces clipboard copy success to screen readers.
 */

(function ($, Drupal, once) {
  'use strict';

  /**
   * Returns the shared ARIA live region, creating it if necessary.
   *
   * role="status" implies aria-live="polite" and aria-atomic="true", so
   * the whole message is read as a unit without interrupting the user.
   *
   * A persistent element outside the AJAX wrapper is used because Drupal
   * AJAX replaces #formatted-citation entirely (replaceWith), which causes
   * some screen readers (e.g. ORCA) to lose track of inline live regions.
   */
  function getLiveRegion() {
    var $region = $('#cite-this-sr-announcement');
    if (!$region.length) {
      $region = $('<div>', {
        id: 'cite-this-sr-announcement',
        role: 'status',
        'aria-live': 'polite',
        'aria-atomic': 'true',
      }).addClass('visually-hidden').appendTo('body');
    }
    return $region;
  }

  /**
   * Writes a message to the live region so screen readers announce it.
   *
   * Clearing before setting gives browsers/SRs a fresh mutation to detect,
   * which is necessary when the same message is announced twice in a row.
   */
  function announce(message) {
    var $region = getLiveRegion();
    $region.empty();
    setTimeout(function () {
      $region.text(message);
    }, 50);
  }

  Drupal.behaviors.citeThisUx = {
    attach: function (context) {
      once('cite-this-ux', 'select[name="csl_list"]', context).forEach(function (select) {
        var $select = $(select);
        var $form = $select.closest('form');
        var $button = $form.find('.clipboard-button');

        // Tracks the currently selected style name for use in announcements.
        var currentStyleName = '';

        // --- Button label + disabled state ---
        function updateButton() {
          var val = $select.val();
          currentStyleName = val ? $select.find('option:selected').text().trim() : '';
          var label = val
            ? Drupal.t('Copy @style citation to clipboard', {'@style': currentStyleName})
            : Drupal.t('Copy citation to clipboard');
          $button.val(label).attr('aria-label', label).prop('disabled', !val);
        }

        updateButton();

        $select.on('change.citeThisUx', function () {
          updateButton();
          // Announce the new selection. On fast connections the MutationObserver
          // fires soon after and its announcement supersedes this one; on slower
          // connections the user hears both in sequence.
          if (currentStyleName) {
            announce(Drupal.t('@style selected', {'@style': currentStyleName}));
          }
        });

        // --- Announce citation preview changes ---
        // Drupal AJAX replaces #formatted-citation entirely (replaceWith), so
        // we observe its parent for childList mutations rather than the element
        // itself. The parent persists across AJAX calls.
        var citationEl = document.getElementById('formatted-citation');
        if (citationEl && citationEl.parentNode) {
          var observer = new MutationObserver(function () {
            var message = currentStyleName
              ? Drupal.t('Citation preview updated, showing @style.', {'@style': currentStyleName})
              : Drupal.t('Citation preview updated.');
            announce(message);
          });
          observer.observe(citationEl.parentNode, {childList: true});
        }

        // --- Announce clipboard copy success ---
        // clipboard.js fires its success event asynchronously; a short delay
        // ensures the copy has completed before we announce it.
        $button.on('click.citeThisUx', function () {
          if (!$(this).prop('disabled')) {
            setTimeout(function () {
              announce(Drupal.t('Citation copied to clipboard.'));
            }, 100);
          }
        });
      });
    }
  };

})(jQuery, Drupal, once);
