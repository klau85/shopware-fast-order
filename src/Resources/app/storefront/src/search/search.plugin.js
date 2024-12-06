import HttpClient from 'src/service/http-client.service';
import Debouncer from 'src/helper/debouncer.helper';
import DomAccess from 'src/helper/dom-access.helper';
import Iterator from 'src/helper/iterator.helper';

const { PluginBaseClass } = window;

export default class SearchPlugin extends PluginBaseClass {

    static options = {
        searchWidgetDelay: 200,
        searchUrlDataAttribute: 'data-url',
        searchResultSelector: '.fast-order-search',
        searchResultItemSelector: '.search-result',
        suggestionClass: 'suggestion'
    };

    init() {
        if (this.el.value.length > 0 && !this.el.classList.contains('is-invalid')) {
            this.el.classList.add(this.options.suggestionClass);
        }
        this.url = DomAccess.getAttribute(this.el, this.options.searchUrlDataAttribute);
        this.client = new HttpClient();
        this.registerEvents();
    }

    registerEvents(){
        this.el.addEventListener(
            'input',
            Debouncer.debounce(this.handleInputEvent.bind(this), this.options.searchWidgetDelay)
        );

        this.el.addEventListener(
            'keyup',
            this.onInputChanged.bind(this)
        );

        document.body.addEventListener(
            'click',
            this.onBodyClick.bind(this)
        );
    }

    handleInputEvent() {
        const value = this.el.value.trim();

        // stop search if minimum input value length has not been reached
        if (value.length < this.el.getAttribute('minlength')) {
            return;
        }

        this.suggest(value);
    }

    onInputChanged(e) {
        this.el.classList.remove(this.options.suggestionClass);
    }

    suggest(value) {
        const url = this.url + encodeURIComponent(value);
        this.client.abort();

        this.client.get(url, (response) => {
            this.clearSearchResults();
            this.el.insertAdjacentHTML('afterend', response);
            let searchResults = this.el.parentNode.querySelectorAll(this.options.searchResultItemSelector);

            searchResults.forEach(item => {
                item.addEventListener('click', this.onSearchResultClick.bind(this));
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

    onSearchResultClick(e) {
        this.el.value = e.target.getAttribute('data-value');
        this.el.classList.add(this.options.suggestionClass);
    }
}