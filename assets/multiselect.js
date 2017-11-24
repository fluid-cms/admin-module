$(function () {
    var options = {
        enableFiltering: true,
        filterPlaceholder: 'Hledat',
        buttonWidth: 'auto',
        nonSelectedText: '-- Vyberte --',
        nSelectedText: 'vybráno',
        allSelectedText: 'vše'
    };

    $('form.fluidForm select:visible:has(option:nth-of-type(5))').multiselect(options);

    options.enableFiltering = false;
    $('form.fluidForm select:visible:has(option:nth-of-type(1))').multiselect(options);
});
