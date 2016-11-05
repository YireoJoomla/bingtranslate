/*
 * Joomla! component - Bing Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

var bingtranslate = new YireoBingTranslate;

/*
 * Quick function to translate by name
 */
function doBingTranslateByName(name) {
    bingtranslate.setEditorByName(name);
    bingtranslate.translate();
}

/*
 * Quick function to translate
 */
function doBingTranslate(editor, language, button) {
    bingtranslate.setEditor(editor);
    bingtranslate.setLanguage(language);
    bingtranslate.setButton(button);
    bingtranslate.translate();
}

/**
 * BingTranslate class
 *
 * @param editor
 * @param language
 * @param button
 * @constructor
 */
function YireoBingTranslate (editor, language, button) {

    /**
     * DOM identifier of current editor, referring to an input, textarea or WYISWYG editor
     * @type string
     */
    this.editor = editor;

    /**
     * Current language string
     * @type string
     */
    this.language = language;

    /**
     * Button pushed when translating
     * @type string
     */
    this.button = button;

    /**
     * Original text of editor
     * @type string
     */
    this.originalText = null;

    /**
     * Flag to determine whether a TinyMCE editor is used or not
     *
     * @type {boolean}
     */
    this.useTinyMCE = false;

    /**
     * Set the language
     *
     * @param language
     */
    this.setLanguage = function (language) {
        this.language = language;
    }

    /**
     * Set the button
     *
     * @param button
     */
    this.setButton = function (button) {
        this.button = button;
    }

    /**
     * Set the editor
     *
     * @param editor
     */
    this.setEditor = function (editor) {
        this.editor = editor;
    };

    /**
     * Set the editor by name
     *
     * @param name
     */
    this.setEditorByName = function (name) {
        this.editor = 'input[name="' + name + '"]';
    };

    /**
     * Detect the current language by inspecting the DOM
     *
     * @returns string
     */
    this.detectLanguage = function () {
        // Support for Joomla! languages
        if (jQuery('#jform_language').length !== 0) {
            this.language = jQuery('#jform_language').val();
        }

        // Support for JoomFish
        if (jQuery('#language_id').length !== 0) {
            this.language = 'joomfish' + jQuery('#language_id').val();
        }

        // Support for VirtueMart
        if (jQuery('#vmlang').length !== 0) {
            this.language = jQuery('#vmlang').val();
        }

        return this.language;
    }

    /**
     * Return the original text of the current editor
     *
     * @returns text
     */
    this.getOriginalText = function() {

        // Fetch the text
        if (textfield = jQuery(this.editor)) {
            originalText = textfield.val();
            if (originalText !== '' && originalText !== undefined) {
                console.log(originalText);
                this.originalText = originalText;
                return this.originalText;
            }
        }

        // Check for TinyMCE
        if (typeof tinyMCE != 'undefined') {

            var tinyMCEEditor = tinyMCE.get(this.editor);

            if (tinyMCEEditor) {
                this.originalText = tinyMCEEditor.getContent();
                this.useTinyMCE = true;
                return this.originalText;
            }

            var tinyMCEEditor = tinyMCE.activeEditor;

            if (tinyMCEEditor) {
                this.originalText = tinyMCEEditor.getContent();
                this.useTinyMCE = true;
                return this.originalText;
            }
        }

        return this.originalText;
    }


    /**
     * Set the loading status
     *
     * @param button
     * @param editor
     */
    this.setLoadingStatus = function () {
        if (this.button) {
            jQuery(this.button).addClass('loading');
            jQuery(this.button).children().attr('class', 'icon-loop');
        }

        if (editor) {
            jQuery(this.editor).parent().children('.bingtranslate-add-on').each(function () {
                jQuery(this).find('i').attr('class', 'icon-loop');
            });
        }
    }

    /**
     * Set the completed status
     *
     * @param button
     * @param editor
     */
    this.setCompleteStatus = function () {
        if (this.button) {
            jQuery(this.button).removeClass('loading');
            jQuery(this.button).children().attr('class', 'icon-copy');
        }

        if (this.editor) {
            jQuery(this.editor).parent().children('.bingtranslate-add-on').each(function () {
                jQuery(this).find('i').attr('class', 'icon-copy');
            });
        }
    }

    /**
     * Set the error status
     *
     * @param button
     * @param editor
     */
    this.setErrorStatus = function () {
        if (this.button) {
            jQuery(this.button).removeClass('loading');
            jQuery(this.button).children().attr('class', 'icon-warning');
        }

        if (this.editor) {
            jQuery(this.editor).parent().children('.bingtranslate-add-on').each(function () {
                jQuery(this).find('i').attr('class', 'icon-warning');
            });
        }
    }

    /**
     * Main translate function
     *
     * @returns {boolean}
     */
    this.translate = function () {

        editor = this.editor;
        language = this.language;
        button = this.button;

        // Loading
        this.setLoadingStatus();

        if (language == '' || language == undefined) {
            language = this.detectLanguage();
        }

        // Do not continue if no language was detected
        if (language == '' || language == undefined) {
            this.setErrorStatus();

            console.log('Failed to detect which language to translate to');
            alert('Failed to detect which language to translate to');
            return false;
        }

        var originalText = this.getOriginalText();

        // Detect whether the text is empty
        if (originalText == '') {
            this.setErrorStatus();
            console.log('No text to translate');
            console.log(originalText);
            return false;
        }

        // Perform the POST
        var postdata = {to: language, text: originalText}; // @todo: Add-in a JToken
        var url = 'index.php?option=com_bingtranslate&task=translate';
        jQuery.ajax({
            type: 'POST',
            url: url,
            data: postdata,
            success: function (data, textStatus) {
                newText = data.text;
                if (data.code == 0) {
                    bingtranslate.setErrorStatus();
                    alert(newText);
                } else {
                    bingtranslate.setCompleteStatus();
                }

                if (data.code == 1 && newText != originalText) {
                    textfield.val(newText);

                    if (typeof isBrowserIE == 'function') {
                        if (isBrowserIE()) {
                            if (bingtranslate.useTinyMCE && window.parent.tinyMCE) {
                                window.parent.tinyMCE.selectedInstance.selection.moveToBookmark(window.parent.global_ie_bookmark);
                            }
                        }
                    }

                    if (bingtranslate.useTinyMCE) {
                        tinyMCE.execCommand('mceSetContent', false, newText);
                    }
                }
            },
            dataType: 'json'
        });

        return false;
    }
}