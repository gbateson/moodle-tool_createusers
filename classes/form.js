//<![CDATA[

function createusers_showHide(btn, hidetext, showtext){

    // "btn" is actually the "e" (event) object
    if (btn.target) {
        btn = btn.target;
    } else if (btn.srcElement) {
        btn = btn.srcElement;
    }
    if (btn.moodle) {
        hidetext = btn.moodle.hidetext;
        showtext = btn.moodle.showtext;
    }

    // locate the FIELDSET object containing the btn that was clicked
    var obj = btn;
    while (obj && obj.tagName != 'FIELDSET') {
        obj = obj.parentNode;
    }

    // toggle visibility
    if (obj) {
        if (obj.new_display) {
            obj.new_display = '';
            btn.innerHTML = hidetext;
        } else {
            obj.new_display = 'none';
            btn.innerHTML = showtext;
        }
        var divs = obj.getElementsByTagName('div');
        var d_max = divs.length;
        for (var d=0; d<d_max; d++) {
            if (divs[d].className.indexOf('fitem')===0) {
                if (divs[d].style) {
                    divs[d].style.display = obj.new_display;
                }
            }
        }
        divs = null;
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
            var divs = obj.getElementsByTagName('div');
            var d_max = divs.length;
            for (var d=0; d<d_max; d++) {
                if (divs[d].className=='advancedbutton') {
                    break;
                }
            }
            if (d<d_max) {
                var btn = document.createElement('button');
                btn.moodle = { 'hidetext' : window.hidetext, 'showtext' : window.showtext }
                btn.appendChild(document.createTextNode(btn.moodle.hidetext));
                btn.onclick = createusers_showHide;
                divs[d].appendChild(btn);
                btn.onclick(btn);
                btn = null;
            }
            divs = null;
        }
        obj = null;
    }
}

createusers_setExpanded(['names', 'defaults', 'display'], false);
//]]>