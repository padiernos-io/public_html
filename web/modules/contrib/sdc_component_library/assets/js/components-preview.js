// Function to handle showing the active component
function setActiveComponent(target) {
  // Hide all components
  document
    .querySelectorAll('.components-preview__components')
    .forEach((component) => {
      component.classList.remove('active');
      component.setAttribute('aria-hidden', 'true'); // Hide for accessibility
    });

  // Remove 'active' class from all titles
  document.querySelectorAll('.components-preview__title').forEach((title) => {
    title.classList.remove('active');
    title.setAttribute('aria-expanded', 'false'); // Collapse all for accessibility
  });

  // Show the clicked component
  const activeComponent = document.getElementById(`component-${target}`);
  if (activeComponent) {
    activeComponent.classList.add('active');
    activeComponent.setAttribute('aria-hidden', 'false'); // Show for accessibility
  }

  // Mark the associated title as active
  const activeTitle = document.querySelector(
    `.components-preview__title[data-target="${target}"]`,
  );
  if (activeTitle) {
    activeTitle.classList.add('active');
    activeTitle.setAttribute('aria-expanded', 'true'); // Mark as expanded for accessibility
  }

  // Save the active component ID to localStorage
  localStorage.setItem('activeComponent', target);
}

// Event listener for clicking the sidebar items
document.querySelectorAll('.components-preview__title').forEach((item) => {
  item.addEventListener('click', function () {
    const target = this.getAttribute('data-target');
    setActiveComponent(target);
  });
});

// Show Code toggle functionality
document
  .querySelectorAll('.components-preview__show-code')
  .forEach((button) => {
    button.addEventListener('click', function () {
      const codeBlockId = this.getAttribute('data-target');
      const codeBlock = document.getElementById(codeBlockId);

      if (codeBlock.style.display === 'none') {
        codeBlock.style.display = 'block';
        this.textContent = 'Hide code'; // Change button text to "Hide code"
      } else {
        codeBlock.style.display = 'none';
        this.textContent = 'Show code'; // Change button text to "Show code"
      }
    });
  });

// On page load, check if there's an active component stored in localStorage
window.addEventListener('DOMContentLoaded', () => {
  const savedComponent = localStorage.getItem('activeComponent');
  if (savedComponent) {
    setActiveComponent(savedComponent);
  } else {
    // Set the first component as active by default if no component is saved
    setActiveComponent('1');
  }
});

document.querySelectorAll('.copy-btn').forEach((button) => {
  button.addEventListener('click', function () {
    const target = this.getAttribute('data-target');
    const codeBlock = document
      .getElementById(target)
      .querySelector('code').innerText;

    // Create a temporary textarea to hold the code
    const tempTextarea = document.createElement('textarea');
    tempTextarea.value = codeBlock;
    document.body.appendChild(tempTextarea);
    tempTextarea.select();
    document.execCommand('copy');
    document.body.removeChild(tempTextarea);

    // Provide feedback to the user
    this.textContent = 'Copied!';
    setTimeout(() => {
      this.textContent = 'Copy';
    }, 2000);
  });
});
document
  .querySelector('.components-preview__search')
  .addEventListener('input', function () {
    const searchValue = this.value.toLowerCase();
    const components = document.querySelectorAll('.components-preview__title');

    components.forEach((component) => {
      const title = component.querySelector('button').textContent.toLowerCase();
      if (title.includes(searchValue)) {
        component.style.display = 'block';
      } else {
        component.style.display = 'none';
      }
    });
  });
