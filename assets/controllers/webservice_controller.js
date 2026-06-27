import { Controller } from '@hotwired/stimulus';

/*
 * Interroge le web service SIRET et affiche la réponse XML inline,
 * sans quitter la page.
 */
export default class extends Controller {
    static targets = ['input', 'result', 'output'];
    static values = { url: String };

    async lookup(event) {
        event.preventDefault();

        const url = new URL(this.urlValue, window.location.origin);
        url.searchParams.set('siret', this.inputTarget.value);

        const response = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
        this.outputTarget.innerHTML = this.highlight(await response.text());
        this.resultTarget.hidden = false;
    }

    highlight(xml) {
        const escaped = xml
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');

        return escaped.replace(/(&lt;[^&]*&gt;)|([^&]+)/g, (match, tag, text) => {
            if (tag) {
                return `<span class="xml-tag">${tag}</span>`;
            }

            return text.trim() ? `<span class="xml-text">${text}</span>` : match;
        });
    }
}
