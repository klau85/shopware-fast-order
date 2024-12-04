const { PluginBaseClass } = window;

export default class FastOrder extends PluginBaseClass {
    init() {
        this.rowsWrapper = this.el.children['rows-wrapper'];
        this._registerEvents();
    }

    _registerEvents()
    {
        this.el.querySelector('#add-row').onclick = this.addRow.bind(this);
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