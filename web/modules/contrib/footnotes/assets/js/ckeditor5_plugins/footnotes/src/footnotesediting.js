/* eslint-disable import/no-unresolved */
import { Plugin } from 'ckeditor5/src/core';
import { toWidget, Widget } from 'ckeditor5/src/widget';
import Footnotescommand from './footnotescommand';

// Function to determine if we should output console logs.
function isDebugMode() {
  const params = new URLSearchParams(window.location.search);
  return params.has('debug');
}

function transformClipboardContent(documentFragment) {
  // Function to create a new <footnotes> element
  function createFootnotesElement(value, reference) {
    const footnotesElement = document.createElement('footnotes');

    footnotesElement.setAttribute('data-value', value);
    footnotesElement.setAttribute('data-text', reference);
    footnotesElement.innerHTML = '&nbsp;'; // or any other content you wish to add

    return footnotesElement;
  }

  // Function to extract the reference html from the selected footnote.
  function extractFootnoteReference(htmlString) {
    // Ensure htmlString is actually a string (not an HTML element)
    if (typeof htmlString !== 'string') {
      throw new Error(`Expected a string as input, got: ${typeof htmlString}`);
    }

    // Create a DOM parser to parse the string
    const parser = new DOMParser();
    const doc = parser.parseFromString(htmlString, 'text/html');

    // Create a document fragment to hold the cleaned content
    const fragment = document.createDocumentFragment();

    // Function to traverse and extract only the relevant elements, namely
    // bold, italics, and links.
    function processNode(node) {
      if (node.nodeType === Node.ELEMENT_NODE) {
        const tagName = node.tagName.toLowerCase();
        if (tagName === 'b' || tagName === 'strong') {
          const bold = document.createElement('b');
          bold.innerHTML = node.innerHTML;
          fragment.appendChild(bold);
        } else if (tagName === 'i' || tagName === 'em') {
          const italic = document.createElement('i');
          italic.innerHTML = node.innerHTML;
          fragment.appendChild(italic);
        } else if (tagName === 'a') {
          const hrefFragment = node.href.split('#')[1] || '';
          // Exclude if the fragment starts with "sdfootnote", "ftn", or "_ftn"
          if (
            !hrefFragment.startsWith('sdfootnote') &&
            !hrefFragment.startsWith('ftn') &&
            !hrefFragment.startsWith('_ftn')
          ) {
            const link = document.createElement('a');
            link.href = node.href;
            link.innerHTML = node.innerHTML;
            fragment.appendChild(link);
          }
        } else if (tagName === 'span') {
          // Unwrap the <span> element by processing its children
          Array.from(node.childNodes).forEach(processNode);
        } else {
          // Recursively extract from child nodes.
          Array.from(node.childNodes).forEach(processNode);
        }
      } else if (node.nodeType === Node.TEXT_NODE) {
        fragment.appendChild(document.createTextNode(node.textContent));
      }
    }

    // Start processing the body of the parsed document.
    Array.from(doc.body.childNodes).forEach(processNode);

    // Convert the document fragment back to HTML
    const container = document.createElement('div');
    container.appendChild(fragment);

    // Return the inner HTML of the cleaned content
    return container.innerHTML.trim();
  }

  if (isDebugMode()) {
    console.log('Content received from paste pipeline:');
    console.log(documentFragment);
  }

  // Find all the footnotes
  const footnotes = documentFragment.querySelectorAll(
    '.sdfootnote, [id*="ftn"] > p, [id*="sdfootnote"] > p',
  );

  footnotes.forEach((footnote) => {
    let footnoteText = extractFootnoteReference(footnote.innerHTML);

    // Find the anchor element
    const anchor = footnote.querySelector('.sdfootnotesym, [ href*="_ftnref"]');
    if (anchor) {
      // Get the link, ensure that it only contains the fragment.
      let footnoteId = anchor.getAttribute('href').replace(/anc|ref|_/g, '');
      footnoteId = `#${footnoteId.split('#').pop()}`;

      // Find the corresponding anchor element and div
      const anchorSup = documentFragment.querySelector(
        `.sdfootnoteanc[href*="${footnoteId}sym"], [href*="_ftn"]`,
      );
      const anchorDiv = documentFragment.querySelector(`div${footnoteId}`);
      const supValue = '';

      if (anchorSup) {
        // Attempt to get footnote text from the anchor div
        // if not found yet.
        if (!footnoteText && anchorDiv.querySelector(`.MsoFootnoteReference`)) {
          // Find the reference number like [1] and remove it so the html
          // remaining is only the reference text itself.
          const anchorReferenceNumber = anchorDiv.querySelector(
            `.MsoFootnoteReference`,
          ).parentNode;

          // If there is a reference number, remove it.
          if (typeof anchorReferenceNumber !== 'undefined') {
            anchorReferenceNumber.parentNode.removeChild(anchorReferenceNumber);
          }
          const anchorDivText = anchorDiv.querySelector('.MsoFootnoteText');
          if (anchorDivText) {
            footnoteText = anchorDivText.innerHTML;
          }
        }

        // If we ultimately have text.
        if (footnoteText) {
          // Create the new drupal footnotes element
          const footnotesElement = createFootnotesElement(
            supValue,
            footnoteText,
          );

          if (isDebugMode()) {
            console.log('Created footnotes element:');
            console.log(footnotesElement);

            console.log('Replacing citation element:');
            console.log(anchorSup);

            console.log('Removing anchor div:');
            console.log(anchorDiv);
          }

          // Remove unwanted remaining html.
          anchorSup.parentNode.replaceChild(footnotesElement, anchorSup);
          anchorDiv.parentNode.removeChild(anchorDiv);
        }
      }
    }
  });

  // Remove any lingering elements.
  const remainingElements = documentFragment.querySelectorAll(
    'div[style*="mso-element:footnote-list;"]',
  );
  if (remainingElements && remainingElements.length > 0) {
    remainingElements.forEach((div) => {
      div.parentNode.removeChild(div);
    });
  }

  // Find all remaining/existing footnotes
  const drupalFootnotes = documentFragment.querySelectorAll('footnotes');

  // Reset the data value for automatic numbering
  drupalFootnotes.forEach((drupalFootnote) => {
    drupalFootnote.setAttribute('data-value', '');
  });

  if (isDebugMode()) {
    console.log('Returning document fragment:');
    console.log(documentFragment);
  }
  return documentFragment;
}

/**
 * Footnotes editing functionality.
 */
export default class Footnotesediting extends Plugin {
  /**
   * @inheritdoc
   */
  static get requires() {
    return [Widget];
  }

  /**
   * @inheritdoc
   */
  static get pluginName() {
    return 'footnotesEditing';
  }

  /**
   * @inheritdoc
   */
  init() {
    this.attrs = {
      footnotesText: 'data-text',
      footnotesValue: 'data-value',
    };
    const options = this.editor.config.get('footnotes');
    if (!options) {
      return;
    }
    const { previewURL, themeError } = options;
    this.previewUrl = previewURL;
    this.themeError =
      themeError ||
      `
        <p>${Drupal.t(
          'An error occurred while trying to preview the embedded content. Please save your work and reload this page.',
        )}<p>
        `;

    this._defineSchema();
    this._defineConverters();

    this.editor.commands.add('footnotes', new Footnotescommand(this.editor));

    // Automatically convert pasted content from Word/LibreOffice to
    // Footnotes format.
    this.editor.plugins.get('ClipboardPipeline').on(
      'inputTransformation',
      (evt, data) => {
        // Convert the view document fragment to a DOM document fragment.
        const viewFragment = data.content;
        const domFragment =
          this.editor.editing.view.domConverter.viewToDom(viewFragment);

        // Apply the footnotes changes and transform it back to a view document fragment.
        const transformedDomFragment = transformClipboardContent(domFragment);
        const transformedViewFragment =
          this.editor.editing.view.domConverter.domToView(
            transformedDomFragment,
          );

        if (isDebugMode()) {
          console.log('Original view fragment:');
          console.log(viewFragment);
          console.log('Transformed view fragment:');
          console.log(transformedViewFragment);
        }

        // Update `data.content` with the transformed view fragment.
        // This will allow further listeners to receive the updated data.
        data.content = transformedViewFragment;
      },
      { priority: 'highest' },
    );
  }

  /**
   * Fetches the preview for the given model element.
   *
   * @param {Element} modelElement - The CKEditor model element representing footnotes.
   */
  async _fetchPreview(modelElement) {
    const query = {
      text: modelElement.getAttribute('footnotesText'),
      value: modelElement.getAttribute('footnotesValue'),
    };
    const response = await fetch(
      `${this.previewUrl}?${new URLSearchParams(query)}`,
    );
    if (response.ok) {
      return response.text();
    }

    return this.themeError;
  }

  /**
   * Registers footnotes as a block element in the DOM converter.
   */
  _defineSchema() {
    const { schema } = this.editor.model;
    schema.register('footnotes', {
      allowWhere: '$inlineObject',
      blockObject: false,
      isObject: true,
      isContent: true,
      isBlock: false,
      isInline: true,
      inlineObject: true,
      allowAttributes: Object.keys(this.attrs),
    });
    this.editor.editing.view.domConverter.blockElements.push('footnotes');
  }

  /**
   * Defines handling of drupal media element in the content lifecycle.
   *
   * @private
   */
  _defineConverters() {
    const { conversion } = this.editor;

    conversion.for('upcast').elementToElement({
      view: {
        name: 'footnotes',
      },
      model: 'footnotes',
    });

    conversion.for('dataDowncast').elementToElement({
      model: 'footnotes',
      view: {
        name: 'footnotes',
      },
    });
    conversion
      .for('editingDowncast')
      .elementToElement({
        model: 'footnotes',
        view: (modelElement, { writer }) => {
          const container = writer.createContainerElement('span');
          return toWidget(container, writer, {
            label: Drupal.t('Footnotes'),
          });
        },
      })
      .add((dispatcher) => {
        const converter = (event, data, conversionApi) => {
          const viewWriter = conversionApi.writer;
          const modelElement = data.item;
          const container = conversionApi.mapper.toViewElement(data.item);
          const footnotes = viewWriter.createRawElement('span', {
            'data-footnotes-preview': 'loading',
            class: 'footnotes-preview',
          });
          viewWriter.insert(
            viewWriter.createPositionAt(container, 0),
            footnotes,
          );
          this._fetchPreview(modelElement).then((preview) => {
            if (!footnotes) {
              return;
            }
            this.editor.editing.view.change((writer) => {
              const footnotesPreview = writer.createRawElement(
                'span',
                {
                  class: 'footnotes-preview',
                  'data-footnotes-preview': 'ready',
                },
                // eslint-disable-next-line max-nested-callbacks
                (domElement) => {
                  domElement.innerHTML = preview;
                },
              );
              writer.insert(
                writer.createPositionBefore(footnotes),
                footnotesPreview,
              );
              writer.remove(footnotes);
            });
          });
        };
        dispatcher.on('attribute:footnotesValue:footnotes', converter);
        return dispatcher;
      });

    Object.keys(this.attrs).forEach((modelKey) => {
      const attributeMapping = {
        model: {
          key: modelKey,
          name: 'footnotes',
        },
        view: {
          name: 'footnotes',
          key: this.attrs[modelKey],
        },
      };
      conversion.for('dataDowncast').attributeToAttribute(attributeMapping);
      conversion.for('upcast').attributeToAttribute(attributeMapping);
    });
  }
}
