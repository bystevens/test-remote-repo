((Drupal, once) => {
  Drupal.behaviors.LayoutBuilderLock = {
    attach() {
      /**
       * Add a checkbox to check all options.
       *
       * @param fieldset
       *   Fieldset of the checkboxes.
       */
      function addCheckAllCheckbox(fieldset) {
        const formItems = fieldset.querySelectorAll('.form-item');
        formItems.forEach(function (item) {
          item.style.display = 'block';
        });

        // Wrapper with checkbox items.
        const formCheckboxes = fieldset.querySelector('.form-checkboxes');

        const select = '.form-checkbox';
        const items = formCheckboxes.querySelectorAll(select);
        const itemsCheck = formCheckboxes.querySelectorAll(`${select}:checked`);

        const defaultChecked = items.length === itemsCheck.length;
        const formItemIdentifier = 'layout-builder-lock-toggle-all';

        const formItem = document.createElement('div');
        const formItemsClasses = ['form-item', 'form-type-checkbox'];
        formItem.classList.add(...formItemsClasses);

        const input = document.createElement('input');
        const inputClasses = [
          'form-checkbox',
          'layout-builder-lock-toggle-all',
        ];

        input.classList.add(...inputClasses);
        input.id = formItemIdentifier;
        input.type = 'checkbox';
        formItem.appendChild(input);

        const label = document.createElement('label');
        label.innerHTML = Drupal.t('Toggle all');
        label.classList.add('option');
        label.setAttribute('for', formItemIdentifier);
        label.setAttribute('checked', defaultChecked.toString());
        formItem.appendChild(label);

        // Add new checkbox as first item to wrapper.
        formCheckboxes.insertBefore(formItem, formCheckboxes.firstChild);

        input.addEventListener('change', function () {
          let checked = false;
          if (this.checked) {
            checked = true;
          }

          const checkboxes = formCheckboxes.querySelectorAll('.form-checkbox');
          checkboxes.forEach(function (checkbox) {
            checkbox.checked = checked;
          });
        });
      }

      const selector = 'fieldset.layout-builder-lock-section-settings';
      const settings = once('layoutBuilderLock', selector);
      settings.forEach(addCheckAllCheckbox);
    },
  };
})(Drupal, once);
