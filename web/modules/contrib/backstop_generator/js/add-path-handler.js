(function ($, Drupal, once) {
  Drupal.behaviors.customPathHandler = {
    attach(context, settings) {
      // Use the once utility from the once library to ensure the event is attached only once.
      once('custom-path-handler', '#add-path-to-list', context).forEach(
        function (element) {
          $(element).on('click', function (event) {
            // Prevent the default form submission action.
            event.preventDefault();

            const nodeTitleInput = $('#node-title');

            // Extract the node title and ID from the string, assuming the format "Title (ID)"
            const match = nodeTitleInput.val().match(/^(.*)\s\((\d+)\)$/);
            if (match) {
              const nodeTitle = match[1]; // The title part
              const nodeId = match[2]; // The ID part

              // Format the output as "Title:node/ID"
              const formattedPath = `${nodeTitle} | node/${nodeId}`;

              // Get current textarea value and append the new path
              const textarea = document.querySelector('#path-list-wrapper textarea');

              if (textarea) {
                const currentPaths = textarea.value.trim();
                const updatedPaths = currentPaths
                    ? `${currentPaths}\n${formattedPath}`
                    : formattedPath;

                // Update the textarea
                textarea.value = updatedPaths;
              }

              // Select the content of the nodeTitleInput field so the user can start typing again
              nodeTitleInput.val('');
              nodeTitleInput.focus().select();
            } else {
              console.log('No valid node ID found in the input');
            }
          });
        },
      );
    },
  };
})(jQuery, Drupal, once);
