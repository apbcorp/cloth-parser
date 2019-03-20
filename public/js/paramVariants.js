var paramVariatsContainer = {
    initCallback: undefined,
    paramVariants: {},
    init: function (callback) {
        this.initCallback = callback;
        this.getParamVariantsList();
    },
    getParamVariantsList: function () {
        $.ajax({
            url: '/api/param_varians/list',
            success: this.successGetParamVariants.bind(this)
        })
    },
    successGetParamVariants: function (data) {
        for (var key in data.result) {
            var values = {};
            data.result[key].forEach(function (value) {
                if (value) {
                    values[value] = value;
                }
            });

            this.paramVariants[key] = values;
        }

        if (this.initCallback !== undefined) {
            this.initCallback();
            this.initCallback = undefined;
        }
    },
    getVariants: function (param) {
        return this.paramVariants[param] === undefined ? {} : this.paramVariants[param];
    }
};