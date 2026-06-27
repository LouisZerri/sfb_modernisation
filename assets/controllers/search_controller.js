import { Controller } from '@hotwired/stimulus';

/*
 * Recherche instantanée d'adhérents : interroge l'endpoint Elasticsearch
 * (debounce 250 ms) et injecte le fragment HTML de résultats.
 */
export default class extends Controller {
    static targets = ['input', 'results', 'clear'];
    static values = { url: String };

    query() {
        this.clearTarget.hidden = this.inputTarget.value === '';
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.fetchResults(), 250);
    }

    clear() {
        this.inputTarget.value = '';
        this.clearTarget.hidden = true;
        this.inputTarget.focus();
        this.fetchResults();
    }

    async fetchResults() {
        const url = new URL(this.urlValue, window.location.origin);
        url.searchParams.set('q', this.inputTarget.value);

        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        this.resultsTarget.innerHTML = await response.text();
    }

    disconnect() {
        clearTimeout(this.timeout);
    }
}
