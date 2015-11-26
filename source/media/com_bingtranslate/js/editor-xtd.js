/*
 * Joomla! component - Bing Translate
 *
 * @author Yireo (info@yireo.com)
 * @copyright Copyright 2015
 * @license GNU Public License
 * @link http://www.yireo.com
 */

function doBingTranslate(editor, language, button) {

    // Loading
    setBingTranslateLoading(button, editor);

    // Support for Joomla! languages
    if (jQuery('#jform_language').length !== 0) {
        language = jQuery('#jform_language').val();
    }

    // Support for JoomFish
    if (jQuery('#language_id').length !== 0) {
        language = 'joomfish' + jQuery('#language_id').val();
    }

    // Support for VirtueMart
    if (jQuery('#vmlang').length !== 0) {
        language = jQuery('#vmlang').val();
    }

    // Do not continue if no language was detected
    if (language == '' || language == undefined) {
        setBingTranslateError(button, editor);
        alert('Failed to detect which language to translate to');
        return false;
    }

    // Fetch the text
    if (textfield = jQuery(editor)) {
        var originalText = textfield.val();
    }

    // Check for TinyMCE
    var useTinyMCE = false;
    if (typeof tinyMCE != 'undefined') {
        var tinyMCEEditor = tinyMCE.get(editor);
        if (tinyMCEEditor) {
            var originalText = tinyMCEEditor.getContent();
            var useTinyMCE = true;
        }
    }

    // Detect whether the text is empty
    if (originalText == '') {
        setBingTranslateError(button, editor);
        console.log('No text to translate');
        return false;
    }

    // Perform the POST
    var postdata = {to: language, text: originalText}; // @todo: Add-in a JToken
    var url = 'index.php?option=com_bingtranslate&task=translate';
    jQuery.ajax({
        type: 'POST',
        url: url,
        data: postdata,
        success: function (data) {
            newText = data.text;
            if (data.code == 0) {
                setBingTranslateError(button, editor);
                alert(newText);
            } else {
                setBingTranslateComplete(button, editor);
            }

            if (data.code == 1 && newText != originalText) {
                textfield.val(newText);

                if (typeof isBrowserIE == 'function') {
                    if (isBrowserIE()) {
                        if (useTinyMCE && window.parent.tinyMCE) {
                            window.parent.tinyMCE.selectedInstance.selection.moveToBookmark(window.parent.global_ie_bookmark);
                        }
                    }
                }

                if (tinyMCEEditor) {
                    tinyMCE.execCommand('mceSetContent', false, newText);
                }
            }
        },
        dataType: 'json'
    });

    return false;
}

function setBingTranslateLoading(button, editor) {
    if (button) {
        jQuery(button).addClass('loading');
        jQuery(button).children().attr('class', 'icon-loop');
    }
    if (editor) {
        jQuery(editor).parent().children('.bingtranslate-add-on').each(function() {
            jQuery(this).find('i').attr('class', 'icon-loop');
        });
    }
}

function setBingTranslateComplete(button, editor) {
    if (button) {
        jQuery(button).removeClass('loading');
        jQuery(button).children().attr('class', 'icon-copy');
    }
    if (editor) {
        jQuery(editor).parent().children('.bingtranslate-add-on').each(function() {
            jQuery(this).find('i').attr('class', 'icon-copy');
        });
    }
}

function setBingTranslateError(button, editor) {
    if (button) {
        jQuery(button).removeClass('loading');
        jQuery(button).children().attr('class', 'icon-warning');
    }
    if (editor) {
        jQuery(editor).parent().children('.bingtranslate-add-on').each(function() {
            jQuery(this).find('i').attr('class', 'icon-warning');
        });
    }
}

