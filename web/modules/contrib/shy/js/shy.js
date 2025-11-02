! function(e, t) {
  "object" == typeof exports && "object" == typeof module ? module.exports = t() : "function" == typeof define && define.amd ? define([], t) : "object" == typeof exports ? exports.CKEditor5 = t() : (e.CKEditor5 = e.CKEditor5 || {}, e.CKEditor5.shy = t())
}(self, (() => (() => {
  var e = {
          "ckeditor5/src/core.js": (e, t, r) => {
              e.exports = r("dll-reference CKEditor5.dll")("./src/core.js")
          },
          "ckeditor5/src/ui.js": (e, t, r) => {
              e.exports = r("dll-reference CKEditor5.dll")("./src/ui.js")
          },
          "dll-reference CKEditor5.dll": e => {
              "use strict";
              e.exports = CKEditor5.dll
          }
      },
      t = {};

  function r(s) {
      var o = t[s];
      if (void 0 !== o) return o.exports;
      var n = t[s] = {
          exports: {}
      };
      return e[s](n, n.exports, r), n.exports
  }
  r.d = (e, t) => {
      for (var s in t) r.o(t, s) && !r.o(e, s) && Object.defineProperty(e, s, {
          enumerable: !0,
          get: t[s]
      })
  }, r.o = (e, t) => Object.prototype.hasOwnProperty.call(e, t);
  var s = {};
  return (() => {
      "use strict";
      r.d(s, {
          default: () => c
      });
      var e = r("ckeditor5/src/core.js");
      class t extends e.Command {
          execute() {
              this.editor.model.change((e => {
                  const r = e.createElement('shy', this.editor.model.document.selection.getAttributes());
                  this.editor.model.insertContent(r);
                  this.editor.editing.view.focus();
              }))
          }
      }
      class o extends e.Plugin {
          _defineSchema() {
              this.editor.model.schema.register("shy", {
                  allowWhere: "$text",
                  isInline: !0,
                  allowAttributesOf: '$text'
              })
          }
          _defineConverters() {
              this.editor.conversion.elementToElement({
                  model: "shy",
                  view: "shy"
              })
          }
          init() {
              const e = this.editor;
              this.editor.commands.add("shy", new t(this.editor)), this._defineSchema(), this._defineConverters(), e.keystrokes.set(["ctrl", 45], ((t, r) => {
                  e.commands.execute("shy"), r()
              }))
          }
      }
      var n = r("ckeditor5/src/ui.js");
      class i extends e.Plugin {
          init() {
              const e = this.editor;
              e.ui.componentFactory.add("shy", (t => {
                  const r = new n.ButtonView(t);
                  return r.set({
                      label: "Insert soft hyphen",
                      icon: '<svg viewBox="0 0 32 30" width="32" height="30" xmlns="http://www.w3.org/2000/svg"><path d="m5.6 14.8c0-3.5 1-6.4 2.6-9.2l2.2.9c-1.4 2.6-2 5.5-2 8.3s.6 5.7 2 8.3l-2.1.9c-1.7-2.8-2.7-5.7-2.7-9.2z"/><path d="m12.3 14.2h7.8v2.2h-7.8z"/><path d="m22.1 23.1c1.4-2.6 2-5.5 2-8.3s-.6-5.7-2-8.3l2.2-.9c1.7 2.8 2.6 5.6 2.6 9.2 0 3.5-1 6.4-2.6 9.2z"/></svg>',
                      tooltip: !0
                  }), r.on("execute", (() => {
                      e.model.change((t => {
                          e.commands.execute("shy")
                      }))
                  })), r
              }))
          }
      }
      class d extends e.Plugin {
          static get pluginName() {
              return "Shy"
          }
          static get requires() {
              return [o, i]
          }
      }
      const c = {
          Shy: d
      }
  })(), s = s.default
})()));
