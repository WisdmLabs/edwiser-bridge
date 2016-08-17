jQuery(document).on("click", "#nm-change-log-id", function () {
    jQuery(".nm-genral-changelog-wrap").attr("class", "nm-genral-changelog-wrap");
    jQuery(".nm-genral-doc-wrap").attr("class", "nm-genral-doc-wrap nm-hide");
    jQuery(".nm-genral-upcom-feth-wrap").attr("class", "nm-genral-upcom-feth-wrap nm-hide");

    jQuery("#nm-change-log-id").attr("class", "nm-sub-menu nm-sub-menu-activ");
    jQuery("#nm-doc-id").attr("class", "nm-sub-menu");
    jQuery("#nm-upcom-featu-id").attr("class", "nm-sub-menu");
});
jQuery(document).on("click", "#nm-doc-id", function () {
    jQuery(".nm-genral-changelog-wrap").attr("class", "nm-genral-changelog-wrap nm-hide");
    jQuery(".nm-genral-doc-wrap").attr("class", "nm-genral-doc-wrap");
    jQuery(".nm-genral-upcom-feth-wrap").attr("class", "nm-genral-upcom-feth-wrap nm-hide");

    jQuery("#nm-change-log-id").attr("class", "nm-sub-menu");
    jQuery("#nm-doc-id").attr("class", "nm-sub-menu nm-sub-menu-activ");
    jQuery("#nm-upcom-featu-id").attr("class", "nm-sub-menu");
});
jQuery(document).on("click", "#nm-upcom-featu-id", function () {
    jQuery(".nm-genral-changelog-wrap").attr("class", "nm-genral-changelog-wrap nm-hide");
    jQuery(".nm-genral-doc-wrap").attr("class", "nm-genral-doc-wrap nm-hide");
    jQuery(".nm-genral-upcom-feth-wrap").attr("class", "nm-genral-upcom-feth-wrap");

    jQuery("#nm-change-log-id").attr("class", "nm-sub-menu");
    jQuery("#nm-doc-id").attr("class", "nm-sub-menu");
    jQuery("#nm-upcom-featu-id").attr("class", "nm-sub-menu nm-sub-menu-activ");
});