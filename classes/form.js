//<![CDATA[

// modified "showHideOnClick" function, which hides only the section containing the button which was clicked
function createusers_showHideOnClick(button, hidetext, showtext){

    // locate the FIELDSET object containing the button that was clicked
    if (window.showHideInit) {
        // Moodle >= 1.9: "button" is actually the "e" (event) object
        if (button.target) {
            button = button.target;
        } else if (button.srcElement) {
            button = button.srcElement;
        }
        hidetext = button.moodle.hideLabel;
        showtext = button.moodle.showLabel;
    }

    var obj = button;
    while (obj && obj.tagName!='FIELDSET') {
        obj = obj.parentNode;
    }

    if (obj) {
        // get all "advanced" elements in this FIELDSET object
        var toSet = findChildNodes(obj, null, 'advanced');

        // get previous show/hide settings
        var last = button.form.elements['mform_showadvanced_last'];
        if (! last) {
            return false;
        }
        if (last.value=='') {
            var lastvalue = 0;
        } else {
            var lastvalue = parseInt(last.value);
        }

        var showhide = 0; // 0=do nothing, 1=show, -1=hide
        if (button.initialized) {
            if (lastvalue & button.bitmask) {
                // this section is currently visible, so hide it
                showhide = -1;
                last.value = (lastvalue & (~button.bitmask));
            } else {
                // this section is currently hidden, so make it visible
                showhide = 1;
                last.value = (lastvalue | button.bitmask);
            }
        } else {
            if (lastvalue && ! (lastvalue & button.bitmask)) {
                // this section is showing but it should be hidden, so hide it
                showhide = -1;
            }
            button.initialized = true;
        }
        switch (showhide) {
            case 1:
                elementShowHide(toSet, true);
                button.value = hidetext;
                break;
            case -1:
                elementShowHide(toSet, false);
                button.value = showtext;
                break;
        }
    }
    //never submit the form if js is enabled.
    return false;
}

function createusers_setExpanded(ids, expanded) {
    var i_max = ids.length;
    for (var i=0; i<i_max; i++) {
        var id = ids[i];
        var obj = document.getElementById(id);
        if (obj) {
            alert('add button to ' + id);
            // add button
        }
    }
}

createusers_setExpanded(['names', 'defaults', 'display'], false);
//]]>