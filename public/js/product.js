function init() {
    productLister.init();
}

var productLister = {
    init: function () {
        this.getProductList();
    },
    getProductList: function () {
        $.ajax({
            url: '/api/project/' + getParam('projectId') + '/product/list',
            success: this.successGetProductList.bind(this)
        })
    },
    successGetProductList: function(data) {
        this.onShow(data.result);
    },
    onShow: function (data) {

    }
};