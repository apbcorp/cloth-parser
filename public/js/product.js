function init() {
    paramVariatsContainer.init(productEditor.init.bind(productEditor));
}

var productEditor = {
    productStatuses: {
        0: 'Новый',
        1: 'Подтвержденный',
        2: 'Отклоненный'
    },
    paramTypes: {
        text: 'text',
        photo: 'photo',
        longtext: 'longtext',
        check: 'checkbox'
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
        table.style.width = '100%';
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

            cell.appendChild(this.generateProductElement(data[key]));
            row.appendChild(cell);
            table.appendChild(row);
        }

        $('body')[0].appendChild(table);

        $('.multiselect').multiselect({
            selectedList: 4, height: 310,
            checkAllText: 'Все',
            uncheckAllText: 'Отмена',
            noneSelectedText: 'Выберите из списка',
            selectedText: '# выбрано',
        });
    },
    generateProductElement: function (product) {
        var productTable = document.createElement('table');
        productTable.style.width = '100%';
        productTable.setAttribute('border', 1);
        productTable.setAttribute('rules', 'cols');
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
            productCell.appendChild(this.generateParameter(product.params[key]));

            productRow.appendChild(productCell);
            column++;

            if (column > this.maxColumns) {
                productTable.appendChild(productRow);
                productRow = document.createElement('tr');
                column = 1;
            }
        }

        if (column !== 1) {
            productTable.appendChild(productRow);
        }

        productRow = document.createElement('tr');
        productCell = document.createElement('td');
        productCell.setAttribute('colspan', this.maxColumns);
        productCell.appendChild(this.generatePhotoCell(photo));
        productRow.appendChild(productCell);
        productTable.appendChild(productRow);
        
        productTable.appendChild(productRow);

        return productTable;
    },
    generateParameter: function (param) {
        var result = document.createElement('div');
        result.style.display = 'grid';

        var label = document.createElement('label');
        label.innerText = param.name;

        result.appendChild(label);

        switch (param.type) {
            case this.paramTypes.text:
                var input = document.createElement('input');
                input.setAttribute('value', param.value);
                input.style.width = '400px';

                result.appendChild(input);

                break;
            case this.paramTypes.longtext:
                var input = document.createElement('textarea');
                input.setAttribute('rows', 5);
                input.style.width = '400px';
                input.innerText = param.value;

                result.appendChild(input);

                break;
            case this.paramTypes.check:
                var input = document.createElement('select');
                input.setAttribute('multiple', 'multiple');
                input.classList.add('multiselect');
                input.style.width = '400px';

                var options = paramVariatsContainer.getVariants(param.name);

                param.value.forEach(function (value) {
                    if (options[value] === undefined) {
                        options[value] = value;
                    }
                });

                for (var key in options) {
                    var option = document.createElement('option');
                    option.innerText = key;
                    option.setAttribute('value', key);

                    if (param.value.indexOf(key) >= 0) {
                        option.setAttribute('selected', 'selected');
                    }

                    input.appendChild(option);
                }

                result.appendChild(input);

                break;
        }

        return result;
    },
    generatePhotoCell: function (photos) {
        var container = document.createElement('table');

        photos.forEach(function (photo) {
            var img = document.createElement('img');
            img.setAttribute('src', photo);
            img.style.maxWidth = '160px';
            img.style.maxHeight = '208px';

            container.appendChild(img);

            var removeButton = document.createElement('button');
            var removeImage = document.createElement('img');
            removeImage.setAttribute('src', '/images/trash.png');
            removeImage.style.width = '32px';
            removeImage.style.height = '32px';
            removeButton.appendChild(removeImage);

            container.appendChild(removeButton);
        });

        return container;
    }
};