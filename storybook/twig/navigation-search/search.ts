import { LitElement, html, customElement } from 'lit-element';

class Search extends LitElement {
  static get properties() {
    return {
      open: Boolean,
      filled: Boolean,
    };
  }

  connectedCallback() {
    super.connectedCallback();
    this.open = false;
    this.filled = false;

    // Listen to the click event on our button.
    const button = this.querySelector('.search__button');
    button.addEventListener('click', this.toggle.bind(this));

    // Listen to changing input in the text field.
    const input = this.querySelector('input');
    input.addEventListener('input', this.inputChange.bind(this));
  }

  render() {
    if (this.open) {
      this.classList.add('open');
    } else {
      this.classList.remove('open');
    }
    if (this.filled) {
      this.classList.add('filled');
    } else {
      this.classList.remove('filled');
    }

    return html`<slot></slot>`;
  }

  inputChange(event) {
    this.filled = event.target.value.length > 0;
  }

  toggle() {
    this.open = !this.open;
  }
}

customElement('so-search')(Search);
