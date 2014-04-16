//<![CDATA[

function createusers_showHide(img, hidetext, showtext){

    // "img" is actually the "e" (event) object
    if (img.target) {
        img = img.target;
    } else if (img.srcElement) {
        img = img.srcElement;
    }

    // locate the FIELDSET object containing the img that was clicked
    var obj = img;
    while (obj && obj.tagName != 'FIELDSET') {
        obj = obj.parentNode;
    }

    // toggle visibility
    if (obj) {
        if (obj.new_display) {
            obj.new_display = '';
            img.src = img.src.replace('collapsed', 'expanded');
        } else {
            obj.new_display = 'none';
            img.src = img.src.replace('expanded', 'collapsed');
        }
        var divs = obj.getElementsByTagName('div');
        var d_max = divs.length;
        for (var d=0; d<d_max; d++) {
            if (divs[d].className.indexOf('fcontainer')>=0) {
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

            var legend = obj.getElementsByTagName('legend');
            if (legend) {
                var txt = document.createTextNode(' ');
                legend[0].insertBefore(txt, legend[0].firstChild);
                txt = null;
                var img = document.createElement('img');
                img.onclick = createusers_showHide;
                img.src = location.href.replace(new RegExp('/admin/tool/.*'), '/pix/t/expanded.png');
                legend[0].insertBefore(img, legend[0].firstChild);
                img.onclick(img);
                img = null;
            }
            legend = null;

            var divs = obj.getElementsByTagName('div');
            var d_max = divs.length - 1;
            for (var d=d_max; d>=0; d--) {
                if (divs[d].className.indexOf('advancedbutton')>=0) {
                    divs[d].parentNode.removeChild(divs[d]);
                }
            }
            divs = null;
        }
        obj = null;
    }
}

createusers_setExpanded(['names', 'defaults', 'display'], false);
//]]>