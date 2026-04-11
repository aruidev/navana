(function () {
    'use strict';

    var FIELDS = ['title', 'tag', 'description', 'link'];
    var DEBOUNCE_MS = 400;

    function canUseLocalStorage() {
        try {
            var probeKey = '__navana_draft_probe__';
            window.localStorage.setItem(probeKey, '1');
            window.localStorage.removeItem(probeKey);
            return true;
        } catch (error) {
            return false;
        }
    }

    function readDraft(storageKey) {
        var raw = window.localStorage.getItem(storageKey);
        if (!raw) {
            return null;
        }

        try {
            var parsed = JSON.parse(raw);
            return parsed && typeof parsed === 'object' ? parsed : null;
        } catch (error) {
            return null;
        }
    }

    function writeDraft(storageKey, form) {
        var payload = {};
        var hasAnyValue = false;

        FIELDS.forEach(function (name) {
            var field = form.elements.namedItem(name);
            if (!field || typeof field.value !== 'string') {
                return;
            }

            payload[name] = field.value;
            if (field.value.trim() !== '') {
                hasAnyValue = true;
            }
        });

        if (!hasAnyValue) {
            window.localStorage.removeItem(storageKey);
            return;
        }

        window.localStorage.setItem(storageKey, JSON.stringify(payload));
    }

    function restoreDraft(storageKey, form) {
        var draft = readDraft(storageKey);
        if (!draft) {
            return;
        }

        FIELDS.forEach(function (name) {
            var field = form.elements.namedItem(name);
            if (!field || typeof field.value !== 'string') {
                return;
            }

            if (Object.prototype.hasOwnProperty.call(draft, name) && typeof draft[name] === 'string') {
                field.value = draft[name];
            }
        });
    }

    function debounce(callback, wait) {
        var timeoutId = null;

        return function () {
            if (timeoutId !== null) {
                window.clearTimeout(timeoutId);
            }

            timeoutId = window.setTimeout(function () {
                callback();
            }, wait);
        };
    }

    function setupDraftForForm(form) {
        var storageKey = form.getAttribute('data-draft-key');
        if (!storageKey) {
            return;
        }

        restoreDraft(storageKey, form);

        var saveDraftDebounced = debounce(function () {
            writeDraft(storageKey, form);
        }, DEBOUNCE_MS);

        form.addEventListener('input', saveDraftDebounced);
        form.addEventListener('change', saveDraftDebounced);
    }

    function init() {
        if (!canUseLocalStorage()) {
            return;
        }

        var forms = document.querySelectorAll('form[data-draft-form="bookmark"][data-draft-key]');
        if (!forms.length) {
            return;
        }

        forms.forEach(setupDraftForForm);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
