import { Controller } from '@hotwired/stimulus';

/*
 * Recherche instantanée d'adhérents : interroge l'endpoint Elasticsearch
 * (debounce 250 ms) et injecte le fragment HTML de résultats.
 */
export default class extends Controller {
    static targets = ['input', 'results', 'spinner'];
    static values = { url: String };

    query() {
        clearTimeout(this.timeout);
        this.timeout = setTimeout(() => this.fetchResults(), 250);
    }

    async fetchResults() {
        const url = new URL(this.urlValue, window.location.origin);
        url.searchParams.set('q', this.inputTarget.value);

        this.spinnerTarget.hidden = false;
        try {
            const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            this.resultsTarget.innerHTML = await response.text();
        } finally {
            this.spinnerTarget.hidden = true;
        }
    }

    disconnect() {
        clearTimeout(this.timeout);
    }
}
