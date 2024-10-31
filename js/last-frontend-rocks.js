(function(oc, last){
    try{
        oc.init();
    } catch (e) {
        const oReq = new XMLHttpRequest();
        oReq.open("GET", last.pixelUrl);
        oReq.send();
    }
})(WP_OctaviusRocks, WP_OctaviusRocks_Last);