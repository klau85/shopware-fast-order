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
        searchResultItemSelector: '.search-result'
    };

    init() {
        this._url = DomAccess.getAttribute(this.el, this.options.searchUrlDataAttribute);
        this._client = new HttpClient();
        this._registerEvents();
    }

    _registerEvents(){
        this.el.addEventListener(
            'input',
            Debouncer.debounce(this._handleInputEvent.bind(this), this.options.searchWidgetDelay)
        );

        document.body.addEventListener(
            'click',
            this._onBodyClick.bind(this)
        );


    }

    _handleInputEvent() {
        const value = this.el.value.trim();

        // stop search if minimum input value length has not been reached
        if (value.length < this.el.getAttribute('minlength')) {
            return;
        }

        this._suggest(value);
    }

    _suggest(value) {
        const url = this._url + encodeURIComponent(value);
        this._client.abort();

        this._client.get(url, (response) => {
            // attach search results to the DOM
            this._clearSearchResults();
            this.el.insertAdjacentHTML('afterend', response);
            let searchResults = this.el.parentNode.querySelectorAll(this.options.searchResultItemSelector);

            searchResults.forEach(item => {
                item.addEventListener('click', this._onSearchResultClick.bind(this));
            });
        });
    }

    _clearSearchResults() {
        const results = document.querySelectorAll(this.options.searchResultSelector);
        Iterator.iterate(results, result => result.remove());
    }

    _onBodyClick(e) {
        if (e.target.closest(this.options.searchResultSelector)) {
            return;
        }

        this._clearSearchResults();
    }

    _onSearchResultClick(e) {
        this.el.value = e.target.getAttribute('data-value');
    }
}