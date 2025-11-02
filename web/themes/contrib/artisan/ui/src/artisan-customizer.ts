import { LitElement, html, unsafeCSS } from 'lit';
import { customElement, state, property, query } from 'lit/decorators.js';
import { live } from 'lit/directives/live.js';
//import style from "./dist-artisan-styleguide.css";

//const cssPath = new URL('./dist-artisan-styleguide.css', Object(import.meta).url).href;

//console.log(cssPath);

import Color from "colorjs.io";

interface CssProperty {
  property: string;
  value: string;
  widget?: string;
  dark_mode_value?: string;
}
interface CssFontDefinition {
  font_face: string;
  font_family: string;
}
@customElement('artisan-customizer')
class ArtisanCustomizer extends LitElement {
  //static styles = unsafeCSS(style);
  @property() cssPropertiesStorageSelector: string = '';
  @property() cssFontDefinitionsStorageSelector: string = '';
  @property() mainCssAssets: string = '';
  @property() componentsCssAssets: string = '';

  @state() private _cssFontDefinitions: CssFontDefinition[] = [];
  @state() private _cssPropertiesDefinitions: CssProperty[] = [];
  @state() private _propertyEdit?: CssProperty;
  @state() private _propertyEditPreviewStyleProperty: string = '';
  @state() private _propertiesFormGropBuilder: { [key: string]: { [key: string]: string; }; } = {};
  @state() private _showOnlyCustomized: boolean = true;

  @state() private _selectedGroups: Set<string> = new Set([]);
  @state() private _searchText: string = '';

  @state() private _modalMessage?: { message: string, error: boolean };

  private _componentsCssAssetsList : string[] = [];
  private _mainCssAssetsList : string[] = [];

  private _detectedCssDefaultProperties: { [key: string]: string } = {};
  private _detectedCssDarkModeProperties: { [key: string]: string } = {};

  private _customizedCssDefaultProperties: { [key: string]: string } = {};
  private _customizedCssDarkModeProperties: { [key: string]: string; } = {};

  private _sortedProperties: string[] = [];

  private _shadowDomProperties: { [key: string]: string; } = {};

  private _selectedGroupsAltered: boolean = false;

  @query('#artisan-customizer-root')
  _componentRoot!: HTMLElement;

  @query('#artisan-customizer-theme-change-controller')
  _componentThemeChangeController!: HTMLInputElement;

  @query('#artisan-properties')
  _propertiesStyleElement!: HTMLStyleElement;
  @query('#artisan-fonts')
  _fontsStyleElement!: HTMLStyleElement;

  firstUpdated() {
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      this._componentRoot.setAttribute('data-theme', 'dark');
    }
    else {
      this._componentRoot.setAttribute('data-theme', 'light');
      this._componentThemeChangeController.checked = true;
    }
  }

  connectedCallback() {
    super.connectedCallback();
    this._initDefinitions();
    this._setDetectedAndCustomizedProperties();
    this._sortedProperties = Object.keys({ ...this._detectedCssDefaultProperties, ...this._detectedCssDarkModeProperties, ...this._customizedCssDefaultProperties, ...this._customizedCssDarkModeProperties }).sort();
    this._setFormBuilder();
  }

  private _themeChange() {
    const current = this._componentRoot.getAttribute('data-theme');
    const change = current == 'light' ? 'dark' : 'light';
    this._componentRoot.setAttribute('data-theme', change);
  }

  private _initDefinitions() {
    this._cssFontDefinitions = JSON.parse((document.querySelector(this.cssFontDefinitionsStorageSelector) as HTMLInputElement).value || '[]');
    this._cssPropertiesDefinitions = JSON.parse((document.querySelector(this.cssPropertiesStorageSelector) as HTMLInputElement).value || '[]');
    this._componentsCssAssetsList = JSON.parse(this.componentsCssAssets || '[]');
    this._mainCssAssetsList = JSON.parse(this.mainCssAssets || '[]');
  }

  private _setDetectedAndCustomizedProperties() {
    this._detectedCssDefaultProperties = {};
    this._detectedCssDarkModeProperties = {};
    this._customizedCssDefaultProperties = {};
    this._customizedCssDarkModeProperties = {};

    Array.from(this.shadowRoot?.styleSheets || []).forEach(sheet => {
      if (!sheet.href || sheet.href.startsWith(window.location.origin)) {
        if (!sheet.ownerNode || (sheet.ownerNode as HTMLElement).classList.contains('artisan-styles')) {
          try {
            if (sheet.cssRules) {
              this._extractCssPropertiesFromRuleList(sheet.cssRules, this._detectedCssDefaultProperties, this._detectedCssDarkModeProperties);
            }
          } catch (e) {
            console.warn('Unable to access stylesheet:', sheet.href);
          }
        }
      }
    });

    // Include from customized properties (detected should emit same result but lets ensure reading main definitions source, also to reduce complexity on "live edit preview").
    this._cssPropertiesDefinitions.forEach(property => {
      this._customizedCssDefaultProperties[property.property] = property.value || '';
      if (property.dark_mode_value) {
        this._customizedCssDarkModeProperties[property.property] = property.dark_mode_value;
      }
    });
  }

  private _extractCssPropertiesFromRuleList(rules: CSSRuleList, cssVariables: { [key: string]: string }, darkModeVariables: { [key: string]: string }) {
    Array.from(rules).forEach(rule => {
      if (rule instanceof CSSStyleRule && rule.selectorText.includes(':root')) {
        const style = rule.style;
        for (let i = 0; i < style.length; i++) {
          const name = style[i];

          if (name.startsWith('--')) {
            if (rule.selectorText.includes('[data-theme="dark"]')) {
              darkModeVariables[name] = style.getPropertyValue(name).trim();
            } else {
              cssVariables[name] = style.getPropertyValue(name).trim();
            }
          }
        }
      } else if (rule instanceof CSSGroupingRule) {
        this._extractCssPropertiesFromRuleList(rule.cssRules, cssVariables, darkModeVariables);
      } else if (rule instanceof CSSMediaRule || rule instanceof CSSSupportsRule) {
        this._extractCssPropertiesFromRuleList(rule.cssRules, cssVariables, darkModeVariables);
      } else if (rule instanceof CSSPropertyRule && rule.name && rule.initialValue) {
        cssVariables[rule.name] = rule.initialValue;
        this._shadowDomProperties[rule.name] = rule.initialValue;
      }
    });
  }

  private _setFormBuilder() {
    // Lets create a form builder to manage the properties.
    this._propertiesFormGropBuilder = this._sortedProperties.reduce((result, propertyName) => {
      const [group, ...rest] = propertyName.replace('--', '').split('-');
      const key = rest.join('-');
      if (!result[group]) {
        result[group] = {};
      }
      result[group][key] = propertyName;
      // Automatically check groups with customizations just when user has marked others.
      if ((this._customizedCssDefaultProperties[propertyName] || this._customizedCssDarkModeProperties[propertyName]) && !this._selectedGroupsAltered) {
        this._selectedGroups.add(group);
      }
      return result;
    }, {} as { [key: string]: { [key: string]: string } });
  }
  private _readableLabel(toConvert: string) {
    const base = toConvert.replace('--', '').replace(/-/g, ' ').replace(/_/g, ' ');
    const camelCase = base.replace(/([A-Z])/g, ' $1');
    return camelCase.charAt(0).toUpperCase() + camelCase.slice(1).toLowerCase();
  }

  renderFormTemplate() {
    return html`
      <form @submit=${(e: Event) => e.preventDefault()} class="space-y-4">
        <details class="p-4 border-t-1 mb-0">
          <summary class="cursor-pointer text-lg font-semibold">Font declarations</summary>
          ${this._cssFontDefinitions.map((property, index) => html`
            <div class="join w-full my-1 mt-5" data-index=${index}>
              <div class="join-item w-full">
                <textarea data-index=${index} required class="textarea w-full" placeholder="Font face: declaration, external import or nothing if already defined in theme." .value=${live(property.font_face)} @input=${(e: Event) => {this._cssFontDefinitions[index].font_face = (e.target as HTMLInputElement).value; this.requestUpdate();}}></textarea>
                <input data-index=${index} required type="text" class="input w-full" placeholder='Font family: "Roboto", sans-serif' .value=${live(property.font_family)} @input=${(e: Event) => {this._cssFontDefinitions[index].font_family = (e.target as HTMLInputElement).value; this.requestUpdate();}} />
              </div>
              <button class="btn btn-warning join-item" title="Remove" @click=${() => {this._cssFontDefinitions = this._cssFontDefinitions.filter((_, i) => {return i !== index}); this.requestUpdate();}}>x</button>
            </div>
          `)}
          <button class="btn btn-success btn-circle my-5" title="Add font declaration" @click=${() => {this._cssFontDefinitions.push(<CssFontDefinition> { font_face: '', font_family: '' }); this.requestUpdate();}}>+</button>
        </details>
        <details class="p-4 border-t-1 mb-0" open>
          <summary class="cursor-pointer text-lg font-semibold">Properties</summary>
          <details class="p-4 mt-5" open>
            <summary class="cursor-pointer text-lg font-semibold">Filters</summary>
              <label class="label mt-5 me-5">
                <input class="input input-bordered w-full" type="text" placeholder="Search properties" .value=${this._searchText} @input=${(e: Event) => {this._searchText = (e.target as HTMLInputElement).value; this.requestUpdate();}} />
              </label>
              <label class="label mt-5">
                <input class="toggle" type="checkbox" .checked=${this._showOnlyCustomized} @change=${(e: Event) => {this._showOnlyCustomized = (e.target as HTMLInputElement).checked; this.requestUpdate();}} />
                Only customized
              </label>
              <fieldset class="fieldset my-3 flex flex-wrap gap-5 w-full">
                <legend class="fieldset-legend">Categories</legend>
                ${Object.keys(this._propertiesFormGropBuilder).map(group => {
                  const groupName = this._readableLabel(group);
                  return html`
                    <label class="fieldset-label" title=${'--' + group}>
                      <input type="checkbox" class="checkbox" .value=${group} .checked=${this._selectedGroups.has(group)} @change=${(e: Event) => {
                        const checkbox = e.target as HTMLInputElement;
                        if (checkbox.checked) {
                          this._selectedGroups.add(group);
                        } else {
                          this._selectedGroups.delete(group);
                        }
                        this._selectedGroupsAltered = true;
                        this.requestUpdate();
                      }} />
                      ${groupName}
                    </label>
                  `;
                })}
              </fieldset>
          </details>
         <button title="Add propery" class="btn btn-success btn-sm mb-5" @click=${(e: Event) => {
          e.preventDefault(); this._propertyManage('', '', '');
        }}>
        <svg class="size-5 shrink-0" width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M20.1005 8.1005L24.3431 12.3431M30 4V10V4ZM39.8995 8.1005L35.6569 12.3431L39.8995 8.1005ZM44 18H38H44ZM39.8995 27.8995L35.6569 23.6569L39.8995 27.8995ZM30 32V26V32ZM20.1005 27.8995L24.3431 23.6569L20.1005 27.8995ZM16 18H22H16Z" stroke="currentColor" stroke-width="4" stroke-linecap="butt" stroke-linejoin="bevel"></path>
            <path d="M29.5856 18.4143L5.54395 42.4559" stroke="currentColor" stroke-width="4" stroke-linecap="butt" stroke-linejoin="bevel"></path>
          </svg> Add customization</button>

          ${Object.keys(this._propertiesFormGropBuilder).filter(group => this._selectedGroups.has(group)).map(group => {
            const groupProperties = Object.keys(this._propertiesFormGropBuilder[group]).filter(key => {
              const propertyName = this._propertiesFormGropBuilder[group][key];
              return (!this._showOnlyCustomized || this._customizedCssDefaultProperties[propertyName] || this._customizedCssDarkModeProperties[propertyName]) &&
                propertyName.includes(this._searchText);
            });
            if (groupProperties.length === 0) {
              return html`
                <details class="p-4 border-t-1" open>
                <summary class="cursor-pointer text-lg font-semibold">${this._readableLabel(group)}</summary>
                  <div class="divider"><span class="text-sm font-normal">Nothing to customize here, try adjusting filters.</span></div>
                </details>
              `;
            }

            return html`
              <details class="p-4 border-t-1" open>
                <summary class="cursor-pointer text-lg font-semibold">${this._readableLabel(group)}</summary>
                <div class="overflow-x-auto">
                  <table class="table w-full mt-2">
                    <tbody>
                    ${groupProperties.map(key => {
                      const propertyName = this._propertiesFormGropBuilder[group][key];
                      const label = this._readableLabel(key || group);
                      const defaultCustomized = this._customizedCssDefaultProperties[propertyName];
                      const darkModeCustomized = this._customizedCssDarkModeProperties[propertyName];
                      const defaultPlaceholder = defaultCustomized || this._detectedCssDefaultProperties[propertyName] || '-';
                      const darkModePlaceholder = darkModeCustomized || this._detectedCssDarkModeProperties[propertyName] || '-';
                      return html`
                      <tr class="hover:bg-base-200">
                      <td>
                        <button class="btn ${defaultCustomized ? 'btn-info' : 'btn-neutral'} btn-sm" @click=${(e: Event) => {e.preventDefault(); this._propertyManage(propertyName, this._detectedCssDefaultProperties[propertyName] || '', this._detectedCssDarkModeProperties[propertyName] || '');}}>
                        <svg class="size-5 shrink-0" width="18" height="18" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                          <path d="M20.1005 8.1005L24.3431 12.3431M30 4V10V4ZM39.8995 8.1005L35.6569 12.3431L39.8995 8.1005ZM44 18H38H44ZM39.8995 27.8995L35.6569 23.6569L39.8995 27.8995ZM30 32V26V32ZM20.1005 27.8995L24.3431 23.6569L20.1005 27.8995ZM16 18H22H16Z" stroke="currentColor" stroke-width="4" stroke-linecap="butt" stroke-linejoin="bevel"></path>
                          <path d="M29.5856 18.4143L5.54395 42.4559" stroke="currentColor" stroke-width="4" stroke-linecap="butt" stroke-linejoin="bevel"></path>
                        </svg> Customize
                        </button>
                      </td>
                      <td class="whitespace-nowrap">
                        <strong>${label[0].toUpperCase() + label.slice(1)}</strong>:</br>${propertyName}
                      </td>
                      <td>
                        <label class="input w-full">
                          <strong>Customize:</strong>
                          <input readonly type="text" class="grow" .placeholder=${defaultPlaceholder} .value=${defaultPlaceholder} @click=${(e: Event) => {e.preventDefault(); this._propertyManage(propertyName, this._detectedCssDefaultProperties[propertyName] || '', this._detectedCssDarkModeProperties[propertyName] || '');}} />
                          <span class="badge ${defaultCustomized ? 'badge-info' : 'badge-neutral'} badge-xs">${defaultCustomized ? 'Customized' : 'Default'}</span>
                        </label>
                      </td>
                      <td>
                        <label class="input w-full">
                          <strong>Dark mode:</strong>
                          <input readonly type="text" class="grow" .placeholder=${darkModePlaceholder} .value=${darkModePlaceholder} @click=${(e: Event) => {e.preventDefault(); this._propertyManage(propertyName, this._detectedCssDefaultProperties[propertyName] || '', this._detectedCssDarkModeProperties[propertyName] || '');}} />
                          <span class="badge ${darkModeCustomized ? 'badge-info' : 'badge-neutral'} badge-xs">${darkModeCustomized ? 'Customized' : 'Default'}</span>
                          </label>
                      </td>
                      </tr>
                      `;
                    })}
                    </tbody>
                  </table>
                </div>
              </details>
            `;
          })}
          <div class="divider"><span class="text-sm font-normal">Note you can adjust filters, default filtered by "only customized" (properties & categories).</span></div>
          <div class="divider"><span class="text-sm font-normal">To see result just save & navigate your site.</span></div>
        </details>
      </form>
    `;
  }



  render() {
    return html`
      <link rel="stylesheet" href="${import.meta.resolve('./artisan-customizer.css')}" />
      <style id="artisan-shadow-dom-properties">
        :root, :host {
          ${Object.keys(this._shadowDomProperties).map((name) => {
            return `${name}: ${this._shadowDomProperties[name]};`;
          }).join('')}
        }
      </style>
      <style id="artisan-fonts"></style>
      <style id="artisan-properties"></style>
      ${this._componentsCssAssetsList.map((asset) => {
        return html`<link rel="stylesheet" class="artisan-styles" href="${asset}" />`;
      })}
      ${this._mainCssAssetsList.map((asset, index, items) => {
        return index === items.length - 1 ? html`<link rel="stylesheet" class="artisan-styles" href="${asset}" @load=${() => {
          this._setDetectedAndCustomizedProperties();
          this._sortedProperties = Object.keys({ ...this._detectedCssDefaultProperties, ...this._detectedCssDarkModeProperties, ...this._customizedCssDefaultProperties, ...this._customizedCssDarkModeProperties }).sort();
          this._setFormBuilder();
        }}/>` : html`<link rel="stylesheet" class="artisan-styles" href="${asset}" />`;
      })}
      <div id="artisan-customizer-root" class="p-5 container" data-theme>
        <label class="flex cursor-pointer gap-2 mb-5">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
          </svg>
          <input type="checkbox" data-toggle-theme="light,dark" data-act-class="active" class="toggle "id="artisan-customizer-theme-change-controller" @change=${() => {this._themeChange();}} />
          <svg
            xmlns="http://www.w3.org/2000/svg"
            width="20"
            height="20"
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round">
            <circle cx="12" cy="12" r="5" />
            <path
              d="M12 1v2M12 21v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M1 12h2M21 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
          </svg>
        </label>

        <h2 class="text-2xl font-bold mb-4">Artisan customizer!</h2>
        ${this.renderFormTemplate()}
        ${this._propertyEdit ? this._renderPropertyManageModal() : ''}
      </div>
    `;
  }

  private _renderPropertyManageWidgetHelper(darkMode: boolean = false) {
    switch (this._propertyEdit!.widget) {
      case 'color':
        let widgetColorHex = '#000000';
        let widgetColorAlpha = 1;
        try {
          const widgetColor = new Color(this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '');
          widgetColorHex = widgetColor.to('srgb').toString({ format: 'hex' }).substring(0, 7);
          widgetColorAlpha = widgetColor.alpha ?? 1;
        } catch (e) {
          //console.error(e);
        }
        return html`
          <input type="color" class="input w-full" .value=${widgetColorHex} style="opacity: ${Math.max(widgetColorAlpha, 0.2)}" @input=${(e: Event) => {
            const newColor = new Color((e.target as HTMLInputElement).value);
            newColor.alpha = widgetColorAlpha;
            this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = newColor.to('oklch').toString();
            this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
            this.requestUpdate();

          }} />
          <input type="range" class="range mt-2 w-full" min="0" max="1" step="0.05" value=${widgetColorAlpha} @input=${(e: Event) => {
            const newColor = new Color(this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '#000000');
            newColor.alpha = parseFloat((e.target as HTMLInputElement).value);
            this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = newColor.to('oklch').toString();
            this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
            this.requestUpdate();
          }} />
        `;
      case 'measure':
        const value = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '';
        const [numericValue, unit] = value.match(/(\d*\.?\d+)([a-z%]*)/)?.slice(1) || ['', ''];
        return html`
          <div class="flex gap-3">
            <input type="number" step="0.05" class="input w-1/2" .value=${numericValue} @input=${(e: Event) => {
              const newValue = (e.target as HTMLInputElement).value + unit;
              this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = newValue;
              this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
              this.requestUpdate();
            }} />
            <select class="select w-1/2" .value=${['px', 'em', 'rem', 'vw', 'vh', '%'].some(unit => value.endsWith(unit)) ? unit : ''} @change=${(e: Event) => {
              const newUnit = (e.target as HTMLSelectElement).value;
              const newValue = numericValue + newUnit;
              this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = newValue;
              this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
              this.requestUpdate();
            }}>
              <option value="">- Unit select / Other -</option>
              <option value="px">px - pixels</option>
              <option value="em">em - relative to parent element</option>
              <option value="rem">rem - relative to root/browser element</option>
              <option value="vw">vw - relative to window width</option>
              <option value="vh">vh - relative to window height</option>
              <option value="%">% - percentage relative to parent element</option>
            </select>
          </div>
        `;
      case 'ratio':
        const ratioValue = (this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '').split('/');
        return html`
          <div class="flex items-center gap-3">
            <input type="number" min="0" step="0.5" class="input w-full" .value=${ratioValue[0] ?? ''} @input=${(e: Event) => {
              const ratioValue = (this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '').split('/');
              this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = (e.target as HTMLInputElement).value + '/' + (ratioValue[1] ?? '');
              this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
              this.requestUpdate();
            }} />
            /
            <input type="number" min="0" step="0.5" class="input w-full" .value=${ratioValue[1] ?? ''} @input=${(e: Event) => {
              const ratioValue = (this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] || '').split('/');
              this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = (ratioValue[0] ?? '') + '/' + (e.target as HTMLInputElement).value;
              this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
              this.requestUpdate();
            }} />
          </div>
        `;
      case 'numeric':
        return html`
          <input type="number" step="0.01" class="input w-full" .value=${this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']} @input=${(e: Event) => {
            this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = (e.target as HTMLInputElement).value;
            this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
            this.requestUpdate();
          }} />
        `;
      case 'font':
        return html`
          <select class="select grow w-full" @change=${(e: Event) => {
            this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = (e.target as HTMLSelectElement).value;
            this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
            this.requestUpdate();
          }}>
            <option value="">- Font select / Other -</option>
            ${this._cssFontDefinitions.map(font => {
              return html`<option value="${font.font_family}">${font.font_family}</option>`;
            })}
          </select>
        `;
      case 'property':
        return html`
            <select class="select w-full" .value=${this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']} @change=${(e: Event) => {
              this._propertyEdit![darkMode ? 'dark_mode_value' : 'value'] = (e.target as HTMLSelectElement).value;
              this._propertyEditPreviewStyleProperty = this._propertyEdit![darkMode ? 'dark_mode_value' : 'value']!;
              this.requestUpdate();
            }}>
              <option value="">- Property select / Other -</option>
              ${Object.keys(this._sortedProperties).map(index => {
                const propertyName = this._sortedProperties[Number(index)];
                const propertyNameReadable = this._sortedProperties[Number(index)].replace('--', '').replace(/-/g, ' ');
                return html`<option value="var(${propertyName})">${propertyNameReadable[0].toUpperCase() + propertyNameReadable.slice(1) + ': ' + propertyName}</option>`
              })}
            </select>
          </div>
        `;
      case '':
      default:
        return html``;
    }
  }

  private _renderPropertyManageModal() {
    return html`
      <div class="modal modal-open">
        <div class="modal-box">
          <button class="btn btn-sm btn-circle btn-ghost absolute right-2 top-2" @click=${this._propertyManageCancel}>✕</button>
          <h3 class="text-lg font-bold">Customize default & dark mode</h3>
          ${this._modalMessage && this._modalMessage.message ? html`
          <div role="alert" class="alert ${this._modalMessage.error ? 'alert-error' : 'alert-info'} mt-5">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-6 w-6 shrink-0 stroke-current">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>${this._modalMessage.message}</span>
          </div>` : html``}
          <div class="py-4">
            <label class="input w-full mt-3">
              <strong class="w-2/5">Name:</strong>
              <input type="text" class="grow" required .value=${this._propertyEdit!.property} title="Property name" placeholder="--[category]-[var]" @input=${(e: Event) => {
                this._propertyEdit!.property = (e.target as HTMLInputElement).value;
                this._modalMessage = {
                  'message': '*Defined property name will be customized on save.',
                  'error': false
                };
              }} />
            </label>
            <small class="mb-5"><strong>Expected</strong>: "--[category]-[var]", where category: "myCategory|my_category" and var: "my-var|myVar|my_var".<br/><strong>Examples</strong>: "--card-text-color|--heroCard-titleColor|--hero_card-title_color".</small>

            <label class="select w-full my-3">
              <strong class="w-2/5">Widget:</strong>
              <select class="grow" @change=${(e: Event) => { this._propertyEdit!.widget = (e.target as HTMLSelectElement).value; this.requestUpdate() }}>
                <option value="" ?selected=${!this._propertyEdit!.widget}>- Select / raw -</option>
                <option value="color" ?selected=${this._propertyEdit!.widget === 'color'}>Color</option>
                <option value="measure" ?selected=${this._propertyEdit!.widget === 'measure'}>Measure</option>
                <option value="ratio" ?selected=${this._propertyEdit!.widget === 'ratio'}>Ratio</option>
                <option value="numeric" ?selected=${this._propertyEdit!.widget === 'numeric'}>Numeric</option>
                <option value="font" ?selected=${this._propertyEdit!.widget === 'font'}>Font</option>
                <option value="property" ?selected=${this._propertyEdit!.widget === 'property'}>Other property reference</option>
              </select>
            </label>

            <div class="divider"><span class="text-xl font-bold">DEFAULT</span></div>

            ${this._renderPropertyManageWidgetHelper(false)}
            <label class="input w-full my-3">
              <strong class="w-2/5">Default:</strong>
              <input type="text" class="grow" .value=${this._propertyEdit!.value || ''} @input=${(e: Event) => { this._propertyEdit!.value = (e.target as HTMLInputElement).value; this._propertyEditPreviewStyleProperty = this._propertyEdit!.value }} />
            </label>

            <div class="divider"><span class="text-xl font-bold">DARK MODE</span></div>

            ${this._renderPropertyManageWidgetHelper(true)}
            <label class="input w-full my-3">
              <strong class="w-2/5">Dark mode:</strong>
              <input type="text" class="grow" .value=${this._propertyEdit!.dark_mode_value || ''} @input=${(e: Event) => { this._propertyEdit!.dark_mode_value = (e.target as HTMLInputElement).value; this._propertyEditPreviewStyleProperty = this._propertyEdit!.dark_mode_value; }} />
            </label>
            <div class="overflow-auto w-full text-center border-t-1 border-b-1 rounded p-5 whitespace-nowrap max-h-40" style="
              background-color: ${this._propertyEditPreviewStyleProperty};
              font-size: ${this._propertyEditPreviewStyleProperty};
              text-decoration: ${this._propertyEditPreviewStyleProperty};
              aspect-ratio: ${this._propertyEditPreviewStyleProperty};
              font-family: ${this._propertyEditPreviewStyleProperty};
              border-radius: ${this._propertyEditPreviewStyleProperty};
              color: black;
              font-weight: bolder;
              text-transform: uppercase;
              text-shadow: 0px 0px 5px white;">
              Preview
            </div>
          </div>
          <div class="modal-action">
            <button class="btn btn-success" @click=${this._propertyManageSave}>Save</button>
            <button class="btn btn-error" @click=${this._propertyManageReset}>Reset to defaults</button>
          </div>
        </div>
      </div>
    `;
  }

  private _propertyManageReset() {
    const propertyIndex = this._cssPropertiesDefinitions.findIndex(property => property.property === this._propertyEdit!.property);
    if (propertyIndex !== -1) {
      this._cssPropertiesDefinitions.splice(propertyIndex, 1);
    }
    this._propertyEdit = undefined;
    this._propertyEditPreviewStyleProperty = '';
    this._setDetectedAndCustomizedProperties();
    this._sortedProperties = Object.keys({ ...this._detectedCssDefaultProperties, ...this._detectedCssDarkModeProperties, ...this._customizedCssDefaultProperties, ...this._customizedCssDarkModeProperties }).sort();
    this._setFormBuilder();

    this.requestUpdate();
  }

  private _propertyManageSave() {
    if (this._propertyEdit) {
      // First check property name or do nothing.
      if (!this._propertyEdit.property || this._propertyEdit.property === '' || !/^--[a-zA-Z\d]+(?:[-_]?[a-zA-Z\d]+)*$/.test(this._propertyEdit.property)) {
        this._modalMessage = {
          'message': 'Invalid property name!',
          'error': true
        };
        return;
      }
      else {
        this._propertyEdit.property = this._propertyEdit.property.trim();
        delete this._modalMessage;
      }
      const propertyIndex = this._cssPropertiesDefinitions.findIndex(property => property.property === this._propertyEdit!.property);
      if (this._propertyEdit.dark_mode_value === '' || this._propertyEdit.dark_mode_value === this._propertyEdit.value) {
        delete this._propertyEdit.dark_mode_value;
      }
      if (propertyIndex !== -1) {
        this._cssPropertiesDefinitions[propertyIndex] = this._propertyEdit;
      } else {
        this._cssPropertiesDefinitions.push(this._propertyEdit);
        if (this._selectedGroupsAltered) {
          this._selectedGroups.add(this._propertyEdit.property.replace('--', '').split('-')[0]);
        }
      }
      this._propertyEdit = undefined;
      this._propertyEditPreviewStyleProperty = '';
      this._setDetectedAndCustomizedProperties();
      this._sortedProperties = Object.keys({ ...this._detectedCssDefaultProperties, ...this._detectedCssDarkModeProperties, ...this._customizedCssDefaultProperties, ...this._customizedCssDarkModeProperties }).sort();
      this._setFormBuilder();
      this.requestUpdate();
    }
  }

  private _propertyManageCancel() {
    this._propertyEdit = undefined;
    this._propertyEditPreviewStyleProperty = '';
    this.requestUpdate();
  }

  private _propertyManage(propertyName: string, value: string, darkModeValue: string) {
    // First try to locate property in _cssPropertiesDefinitions.
    const propertyIndex = this._cssPropertiesDefinitions.findIndex(property => property.property === propertyName);
    if (propertyIndex !== -1) {
      this._propertyEdit = {...this._cssPropertiesDefinitions[propertyIndex]};
    }
    else {
      this._propertyEdit = {
        property: propertyName,
        value: value,
      };
      const widgetPreset = this._propertyManageWidgetPresetFromValue(value);
      if (widgetPreset) {
        this._propertyEdit.widget = widgetPreset;
      }
      if (darkModeValue) {
        this._propertyEdit.dark_mode_value = darkModeValue;
      }
    }
  }

  private _propertyManageWidgetPresetFromValue(value: string) {
    let wigwetFromValue = '';
    if (value.startsWith('var(--')) {
      return 'property';
    }
    if (['px', 'em', 'rem', 'vw', 'vh', '%'].some(unit => value.endsWith(unit))) {
      return 'measure';
    }
    if (/^\d+\.?\d*\/\d+\.?\d*$/.test(value)) {
      return 'ratio';
    }
    if (!isNaN(parseFloat(value))) {
      return 'numeric';
    }
    if (this._cssFontDefinitions.some(font => font.font_family === value)) {
      return 'font';
    }
    try {
      new Color(value);
      return 'color';
    } catch (e) {
      // console.error(e);
    }
    return wigwetFromValue;
  }

  private _liveSyles() {
    const fontStyles = this._cssFontDefinitions.map(font => {
      return font.font_face;
    }).join('');
    this._fontsStyleElement.textContent = fontStyles;

    const defaultMode = ':root, :host, [data-theme=light] {' + this._cssPropertiesDefinitions.map(property => {
      if (property.value) {
        return `${property.property}: ${property.value};`
      }
      return '';
    }).join('') + '}';
    const darkMode = ':root[data-theme="dark"], :host[data-theme="dark"], [data-theme="dark"] {' + this._cssPropertiesDefinitions.map(property => {
      if (property.dark_mode_value) {
        return `${property.property}: ${property.dark_mode_value};`
      }
      return '';
    }).join('') + '}';
    const propertiesStyles = defaultMode + darkMode;
    this._propertiesStyleElement.textContent = propertiesStyles;
  }

  updated() {
    this._liveSyles();
    this._liveStorage();
  }

  private _liveStorage() {
    (document.querySelector(this.cssFontDefinitionsStorageSelector) as HTMLInputElement).value = JSON.stringify(this._cssFontDefinitions);
    (document.querySelector(this.cssPropertiesStorageSelector) as HTMLInputElement).value = JSON.stringify(this._cssPropertiesDefinitions);
  }

}

