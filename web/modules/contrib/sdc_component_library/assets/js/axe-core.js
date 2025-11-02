document.addEventListener('DOMContentLoaded', function () {
  // Function to poll until axe-core is loaded
  function waitForAxeCore(callback) {
    if (typeof axe !== 'undefined') {
      callback();
    } else {
      console.log('Waiting for axe-core to load...');
      setTimeout(() => waitForAxeCore(callback), 100); // Check every 100ms
    }
  }

  // Function to highlight the elements with issues
  function highlightIssueElement(nodes) {
    nodes.forEach((node) => {
      const element = document.querySelector(node.target.join(', '));
      if (element) {
        element.style.outline = '2px solid red'; // Add a red outline to highlight the element
        element.scrollIntoView({ behavior: 'smooth', block: 'center' }); // Smooth scroll to the element
      }
    });
  }

  // Function to remove highlight from elements
  function removeHighlightFromElements(nodes) {
    nodes.forEach((node) => {
      const element = document.querySelector(node.target.join(', '));
      if (element) {
        element.style.outline = ''; // Remove the outline
      }
    });
  }

  // Function to run a11y tests on the preview area of a specific component
  function runA11yTest(targetComponent) {
    const previewArea = targetComponent.querySelector(
      '.components-preview__preview',
    );

    if (!previewArea) {
      console.error('Preview area not found for the selected component.');
      return;
    }

    axe.run(previewArea, function (err, results) {
      if (err) throw err;

      // Get the results panel and list
      const resultsPanel = document.getElementById('a11y-results-panel');
      const resultsList = document.getElementById('a11y-results-list');
      resultsList.innerHTML = ''; // Clear previous results

      // Show the results panel
      resultsPanel.style.display = 'block';

      // If there are violations, display them
      if (results.violations.length > 0) {
        results.violations.forEach((violation) => {
          const listItem = document.createElement('li');
          listItem.textContent = `${violation.help}: ${violation.description}`;
          resultsList.appendChild(listItem);

          // Highlight element when hovering over the issue
          listItem.addEventListener('mouseenter', function () {
            highlightIssueElement(violation.nodes);
          });

          listItem.addEventListener('mouseleave', function () {
            removeHighlightFromElements(violation.nodes);
          });
        });
      } else {
        const listItem = document.createElement('li');
        listItem.textContent = 'No accessibility issues found!';
        resultsList.appendChild(listItem);
      }
    });
  }

  // Attach event listeners to sidebar titles to trigger the a11y test on click
  function initializeA11yTests() {
    const sidebarTitles = document.querySelectorAll(
      '.components-preview__title',
    );
    sidebarTitles.forEach((title) => {
      title.addEventListener('click', function () {
        // Get the target component's ID based on the data-target attribute
        const targetIndex = title.getAttribute('data-target');
        const targetComponent = document.querySelector(
          `#component-${targetIndex}`,
        );

        if (targetComponent) {
          runA11yTest(targetComponent); // Run the a11y test on the active component
        } else {
          console.error(
            `Component with ID #component-${targetIndex} not found.`,
          );
        }
      });
    });
  }

  // Wait for axe-core to be loaded, then initialize the tests
  waitForAxeCore(initializeA11yTests);
});
