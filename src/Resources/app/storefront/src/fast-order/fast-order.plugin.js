const { PluginBaseClass } = window;

export default class FastOrder extends PluginBaseClass {

    static options = {
        searchFieldSelector: '.search-field',
        selectedSuggestionClass: 'suggestion',
        formErrorSelector: '.error',
    }

    init() {
        this.rowsWrapper = this.el.children['rows-wrapper'];
        this._registerEvents();
    }

    _registerEvents()
    {
        this.el.addEventListener('submit', this.onSubmit.bind(this));
        this.el.querySelector('#add-row').onclick = this.addRow.bind(this);
    }

    onSubmit(e){
        let searchFields = this.el.querySelectorAll(this.options.searchFieldSelector);

        for (const item of searchFields) {
            if (!item.classList.contains(this.options.selectedSuggestionClass)) {
                e.preventDefault();
                this.el.querySelector(this.options.formErrorSelector).classList.remove('d-none');
                break;
            }
        }
    }

    addRow(event)
    {
        let rowsCount = this.rowsWrapper.childElementCount;
        let originalRow = this.rowsWrapper.children[0];

        let newRow = originalRow.cloneNode(true);

        let productNumberInput = newRow.querySelector('.form-group .product-number');
        productNumberInput.id = 'product-number-' + (rowsCount + 1);
        productNumberInput.value = '';
        productNumberInput.name = "productNumber_"+ (rowsCount + 1);

        let quantityInput = newRow.querySelector('.form-group .quantity');
        quantityInput.id = 'quantity-' + (rowsCount + 1);
        quantityInput.value = 1;
        quantityInput.name = 'quantity_' + (rowsCount + 1);

        this.rowsWrapper.appendChild(newRow);
    }
}