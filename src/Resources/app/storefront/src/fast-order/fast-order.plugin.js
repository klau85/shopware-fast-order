import HttpClient from 'src/service/http-client.service';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';

const { PluginBaseClass } = window;

export default class FastOrder extends PluginBaseClass {

    static options = {
        searchFieldSelector: '.search-field',
        selectedSuggestionClass: 'suggestion',
        formErrorSelector: '.error',
        searchWidgetDelay: 200,
        searchUrlDataAttribute: 'data-url',
        searchResultSelector: '.fast-order-search',
        searchResultItemSelector: '.search-result',
    }

    init() {
        this.rowsWrapper = this.el.children['rows-wrapper'];

        this.searchInputs = this.el.querySelectorAll(this.options.searchFieldSelector);
        this.initSearchInputs();

        this.client = new HttpClient();

        this._registerEvents();
    }

    initSearchInputs()
    {
        for (const item of this.searchInputs) {
            if (item.value.length > 0 && !item.classList.contains('is-invalid')) {
                item.classList.add(this.options.selectedSuggestionClass);
            }

            this.registerInputEvents(item);
        }
    }

    _registerEvents()
    {
        this.el.addEventListener('submit', this.onSubmit.bind(this));
        this.el.querySelector('#add-row').onclick = this.addRow.bind(this);

        document.body.addEventListener(
            'click',
            this.onBodyClick.bind(this)
        );
    }

    registerInputEvents(input)
    {
        input.addEventListener(
            'input',
            (e) => { this.handleInputEvent(input, e); }
        );

        input.addEventListener(
            'keyup',
            (e) => { this.onInputChanged(input, e); }
        );
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
        productNumberInput.classList.remove(this.options.selectedSuggestionClass);
        productNumberInput.name = "productNumber_"+ (rowsCount + 1);
        this.registerInputEvents(productNumberInput);

        let quantityInput = newRow.querySelector('.form-group .quantity');
        quantityInput.id = 'quantity-' + (rowsCount + 1);
        quantityInput.value = 1;
        quantityInput.name = 'quantity_' + (rowsCount + 1);

        this.rowsWrapper.appendChild(newRow);
    }

    handleInputEvent(input, e) {
        const value = input.value.trim();

        // stop search if minimum input value length has not been reached
        if (value.length < input.getAttribute('minlength')) {
            return;
        }

        this.suggest(input, value);
    }

    onInputChanged(input, e) {
        input.classList.remove(this.options.selectedSuggestionClass);
    }

    suggest(input, value) {
        const url = DomAccess.getAttribute(input, this.options.searchUrlDataAttribute) + encodeURIComponent(value);
        this.client.abort();

        this.client.get(url, (response) => {
            this.clearSearchResults();
            input.insertAdjacentHTML('afterend', response);
            let searchResults = input.parentNode.querySelectorAll(this.options.searchResultItemSelector);

            searchResults.forEach(item => {
                item.addEventListener('click', (e) => {
                    this.onSearchResultClick(input, e);
                });
            });
        });
    }

    clearSearchResults() {
        const results = document.querySelectorAll(this.options.searchResultSelector);
        Iterator.iterate(results, result => result.remove());
    }

    onBodyClick(e) {
        if (e.target.closest(this.options.searchResultSelector)) {
            return;
        }

        this.clearSearchResults();
    }

    onSearchResultClick(input, e) {
        input.value = e.target.getAttribute('data-value');
        input.classList.add(this.options.selectedSuggestionClass);
    }
}