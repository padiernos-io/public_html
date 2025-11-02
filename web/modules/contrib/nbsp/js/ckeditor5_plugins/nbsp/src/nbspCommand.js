import { Command } from "ckeditor5/src/core";

export default class NbspCommand extends Command {
  execute() {
    const editor = this.editor;
    const selection = editor.model.document.selection;

    // Insert tag in the current selection location.
    editor.model.change((writer) => {
      // Create a <nbsp> element with all the selection attributes.
      const placeholder = writer.createElement("nbsp", {
        ...Object.fromEntries(selection.getAttributes()),
      });

      // Insert it into the document. Put the selection on the inserted element.
      editor.model.insertObject(placeholder, null, null, {
        setSelection: "on",
      });
    });
  }
}
