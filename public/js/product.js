function init() {
    productLister.init();
}

var productLister = {
    productStatuses: {
        0: 'Новый',
        1: 'Подтвержденный',
        2: 'Отклоненный'
    },
    paramTypes: {
        text: 'text',
        photo: 'photo',
        longtext: 'longtext',
        textOrCheck: 'textOrCheckbox'
    },
    status: 0,
    maxColumns: 2,
    init: function () {
        this.getProductList();
    },
    getProductList: function () {
        var data = {
            status: this.status
        };

        $.ajax({
            url: '/api/project/' + getParam('projectId') + '/product/list',
            data: data,
            success: this.successGetProductList.bind(this)
        })
    },
    successGetProductList: function(data) {
        this.onShow(data.result);
    },
    onShow: function (data) {
        var table = document.createElement('table');
        var row = document.createElement('tr');
        var cell = document.createElement('td');

        var filterTable = document.createElement('table');
        var filterRow = document.createElement('tr');
        var filterCell = document.createElement('td');
        var filterSelector = document.createElement('select');
        var option = undefined;

        for (var key in this.productStatuses) {
            option = document.createElement('option');
            option.innerText = this.productStatuses[key];
            option.setAttribute('value', key);

            filterSelector.appendChild(option);
        }

        filterCell.appendChild(filterSelector);
        filterRow.appendChild(filterCell);
        filterTable.appendChild(filterRow);

        cell.appendChild(filterTable);
        row.appendChild(cell);
        table.appendChild(row);

        for (key in data) {
            row = document.createElement('tr');
            cell = document.createElement('td');

            cell.appendChild(this.showProductElement(data[key]));
            row.appendChild(cell);
            table.appendChild(row);
        }

        $('body')[0].appendChild(table);
    },
    showProductElement: function (product) {
        var productTable = document.createElement('table');
        var productRow = document.createElement('tr');
        var productCell = document.createElement('td');
        productCell.setAttribute('colspan', 2);
        productCell.style.textAlign = 'right';
        var link = document.createElement('a');
        link.setAttribute('href', product.link);
        link.innerText = '# ' + product.id;
        productCell.appendChild(link);
        productRow.appendChild(productCell);
        productTable.appendChild(productRow);

        productRow = document.createElement('tr');

        var column = 1;
        var photo = [];
        for (var key in product.params) {
            if (product.params[key].type === this.paramTypes.photo) {
                photo = product.params[key].value;

                continue;
            }

            productCell = document.createElement('td');
            productCell.appendChild(this.showParameter(product.params[key]));

            productRow.appendChild(productCell);
            column++;

            if (column > this.maxColumns) {
                productTable.appendChild(productRow);
                productRow = document.createElement('tr');
            }
        }

        if (column !== 1) {
            productTable.appendChild(productRow);
        }

        return productTable;
    },
    showParameter: function (param) {
        var result = document.createElement('div');

        switch (param.type) {
            case this.paramTypes.text:
                var label = document.createElement('label');
                label.innerText = param.name;

                var input = document.createElement('input');
                input.setAttribute('value', param.value);

                result.appendChild(label);
                result.appendChild(input);
        }

        return result;
    }
};