<div class="html-editor" data-html-editor>
<label for="description-source">Opis *</label>
<div class="html-editor-toolbar" role="toolbar" aria-label="Formatowanie opisu" data-editor-toolbar hidden>
<button type="button" data-editor-command="bold" title="Pogrubienie"><strong>B</strong></button><button type="button" data-editor-command="italic" title="Kursywa"><em>I</em></button><button type="button" data-editor-command="underline" title="Podkreślenie"><u>U</u></button>
<button type="button" data-editor-command="formatBlock" data-editor-value="p">Akapit</button><button type="button" data-editor-command="formatBlock" data-editor-value="h2">Nagłówek H2</button><button type="button" data-editor-command="formatBlock" data-editor-value="h3">Nagłówek H3</button>
<button type="button" data-editor-command="insertUnorderedList">Lista •</button><button type="button" data-editor-command="insertOrderedList">Lista 1.</button><button type="button" data-editor-link>Link</button><button type="button" data-editor-command="unlink">Usuń link</button><button type="button" data-editor-command="removeFormat">Usuń formatowanie</button><button type="button" data-editor-source aria-pressed="false">HTML</button>
</div>
<div class="html-editor-link" data-editor-link-panel hidden><label>Adres linku<input type="url" data-editor-link-url disabled maxlength="2048" placeholder="https://..."></label><button type="button" data-editor-link-apply>Dodaj link</button><button type="button" data-editor-link-cancel>Anuluj</button></div>
<div class="html-editor-surface rich-description" contenteditable="true" role="textbox" aria-label="Opis" aria-multiline="true" aria-required="true" data-editor-visual hidden></div>
<textarea id="description-source" name="description" rows="10" required maxlength="15000" data-editor-textarea>{{ $value }}</textarea>
<p class="field-error" data-editor-error role="alert" hidden></p><small>Formatuj tekst przyciskami lub przełącz na HTML. Obsługiwane są nagłówki, akapity, listy, linki i tabele. Maksymalnie 15 000 znaków kodu.</small>
</div>
