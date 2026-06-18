/*
 * Sammeldownload-Auswahl (sw_zip).
 * Verdrahtet die "Alle auswählen"-Checkbox mit den Treffer-Checkboxen.
 * Bewusst ohne Inline-Handler (CSP-konform) und ohne Framework-Abhängigkeit.
 */
(function () {
    'use strict';

    function initForm(form) {
        var master = form.querySelector('.sw-zip-all');
        var items = function () {
            return Array.prototype.slice.call(form.querySelectorAll('.sw-zip-item'));
        };

        function syncMaster() {
            if (!master) {
                return;
            }
            var all = items();
            var checked = all.filter(function (i) { return i.checked; }).length;
            master.checked = checked > 0 && checked === all.length;
            master.indeterminate = checked > 0 && checked < all.length;
        }

        if (master) {
            master.addEventListener('change', function () {
                items().forEach(function (i) { i.checked = master.checked; });
            });
        }

        form.addEventListener('change', function (event) {
            if (event.target && event.target.classList.contains('sw-zip-item')) {
                syncMaster();
            }
        });

        form.addEventListener('submit', function (event) {
            var any = items().some(function (i) { return i.checked; });
            if (!any) {
                event.preventDefault();
            }
        });

        syncMaster();
    }

    function init() {
        Array.prototype.slice
            .call(document.querySelectorAll('.sw-zip-form'))
            .forEach(initForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
