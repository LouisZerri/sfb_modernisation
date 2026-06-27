import { Controller } from '@hotwired/stimulus';

/*
 * Affiche le champ « Date de réception » uniquement quand la case
 * « Bulletin reçu ? » est cochée.
 */
export default class extends Controller {
    static targets = ['date', 'checkbox'];

    connect() {
        this.toggle();
    }

    toggle() {
        this.dateTarget.hidden = !this.checkboxTarget.checked;
    }
}
