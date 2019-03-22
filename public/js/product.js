function init() {
    paramVariatsContainer.init(productEditor.init.bind(productEditor));
}

var productEditor = {
    productStatuses: {
        0: 'Новый',
        1: 'Подтвержденный',
        2: 'Отклоненный'
    },
    approveProductStatus: 1,
    statusButtons: {
        0: {1: 'Подтвердить', 2: 'Отклонить'},
        1: {2: 'Отклонить'},
        2: {1: 'Подтвердить'},
    },
    paramTypes: {
        text: 'text',
        photo: 'photo',
        longtext: 'longtext',
        multiselect: 'multiselect'
    },
    arrayParamTypes: [],
    classes: {
        imageRemoveButton: 'js_imgRemove',
        photo: 'js_img',
        changeStatus: 'js_changeStatus'
    },
    status: 0,
    maxColumns: 2,
    init: function () {
        this.arrayParamTypes.push(this.paramTypes.multiselect);
        this.arrayParamTypes.push(this.paramTypes.photo);

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

        this.bindActions();
    },
    bindActions: function () {
        var deleteImageButtons = $('.' + this.classes.imageRemoveButton);
        var changeStatusButtons = $('.' + this.classes.changeStatus);
        deleteImageButtons.off();
        changeStatusButtons.off();

        deleteImageButtons.on('click', this.removeImageAction.bind(this));
        changeStatusButtons.on('click', this.changeStatusAction.bind(this));
    },
    generateProductElement: function (product) {
        var productTable = document.createElement('table');
        productTable.setAttribute('data-product-id', product.id);
        productTable.style.width = '100%';
        productTable.setAttribute('border', 1);
        productTable.setAttribute('rules', 'cols');
        var productRow = document.createElement('tr');

        var productCell = document.createElement('td');
        this.appendChangeStatusButtons(productCell, product.id);
        productRow.appendChild(productCell);

        productCell = document.createElement('td');
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
        var photoParamName = undefined;
        for (var key in product.params) {
            if (product.params[key].type === this.paramTypes.photo) {
                photo = product.params[key].value;
                photoParamName = product.params[key].name;

                continue;
            }

            productCell = document.createElement('td');
            productCell.appendChild(this.generateParameter(product.params[key], product.id));

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
        productCell.appendChild(this.generatePhotoCell(product.id, photo, photoParamName));
        productRow.appendChild(productCell);
        productTable.appendChild(productRow);
        
        productTable.appendChild(productRow);

        return productTable;
    },
    generateParameter: function (param, productId) {
        var result = document.createElement('div');
        result.style.display = 'grid';

        var label = document.createElement('label');
        label.innerText = param.name;

        result.appendChild(label);

        switch (param.type) {
            case this.paramTypes.text:
                var input = document.createElement('input');
                input.setAttribute('value', param.value);
                input.setAttribute('data-name', param.name);
                input.setAttribute('data-type', param.type);
                input.setAttribute('data-product-id', productId);
                input.style.width = '400px';

                result.appendChild(input);

                break;
            case this.paramTypes.longtext:
                var input = document.createElement('textarea');
                input.setAttribute('rows', 5);
                input.setAttribute('data-name', param.name);
                input.setAttribute('data-type', param.type);
                input.setAttribute('data-product-id', productId);
                input.style.width = '400px';
                input.innerText = param.value;

                result.appendChild(input);

                break;
            case this.paramTypes.multiselect:
                var input = document.createElement('select');
                input.setAttribute('multiple', 'multiple');
                input.setAttribute('data-name', param.name);
                input.setAttribute('data-type', param.type);
                input.setAttribute('data-product-id', productId);
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
    generatePhotoCell: function (productId, photos, name) {
        var container = document.createElement('table');
        var $this = this;

        photos.forEach(function (photo, index) {
            var img = document.createElement('img');
            img.setAttribute('src', photo);
            img.style.maxWidth = '160px';
            img.style.maxHeight = '208px';
            img.classList.add($this.classes.photo);
            img.setAttribute('data-product-id', productId);
            img.setAttribute('data-index', index);
            img.setAttribute('data-name', name);
            img.setAttribute('data-type', $this.paramTypes.photo);

            container.appendChild(img);

            var removeButton = document.createElement('button');
            removeButton.setAttribute('data-product-id', productId);
            removeButton.setAttribute('data-index', index);
            removeButton.classList.add($this.classes.imageRemoveButton);
            var removeImage = document.createElement('img');
            removeImage.setAttribute('src', '/images/trash.png');
            removeImage.style.width = '32px';
            removeImage.style.height = '32px';
            removeButton.appendChild(removeImage);

            container.appendChild(removeButton);
        });

        return container;
    },
    appendChangeStatusButtons: function (cell, productId) {
        var buttons = this.statusButtons[this.status];

        for (var buttonStatus in buttons) {
            var button = document.createElement('button');
            button.innerText = buttons[buttonStatus];
            button.setAttribute('data-product-id', productId);
            button.setAttribute('data-target-status', buttonStatus);
            button.classList.add(this.classes.changeStatus);

            cell.appendChild(button);
            cell.innerHTML += '&nbsp;&nbsp;&nbsp;';
        }
    },
    removeImageAction: function (event) {
        var targetDataset = event.currentTarget.dataset;
        var selector = '.' + this.classes.photo + '[data-product-id="' + targetDataset.productId + '"]';
        selector += '[data-index="' + targetDataset.index + '"]';
        var imageElement = $(selector);

        imageElement.remove();
        event.currentTarget.remove();
    },
    changeStatusAction: function (event) {
        var dataset = event.currentTarget.dataset;
        if (parseInt(dataset.targetStatus) === this.approveProductStatus) {
            this.approveAction(event);

            return;
        }

        var url = '/api/project/' + getParam('projectId') + '/product/' + dataset.productId;
        url += '/status_change/' + dataset.targetStatus;

        $.ajax({
            url: url,
            method: 'POST',
            success: this.successChangeStatus.bind(this)
        });
    },
    approveAction: function (event) {
        var dataset = event.currentTarget.dataset;
        var fields = $('[data-product-id="' + dataset.productId + '"][data-name]');
        var $this = this;

        var formData = {};
        fields.toArray().forEach(function (field) {
            var value = undefined;
            var key = undefined;

            if (field.options === undefined) {
                value = field.value === undefined ? field.src : field.value;
            } else {
                value = [];
                for (key in field.options) {
                    if (field.options[key].selected === true) {
                        value.push(field.options[key].value);
                    }
                }
            }

            if (formData[field.dataset.name] === undefined) {
                formData[field.dataset.name] = {
                    type: field.dataset.type,
                    value: $this.arrayParamTypes.indexOf(field.dataset.type) >= 0 && !Array.isArray(value)
                        ? [value]
                        : value
                }
            } else {
                if ($this.arrayParamTypes.indexOf(field.dataset.type) >= 0) {
                    if (Array.isArray(value)) {
                        formData[field.dataset.name].value = value;
                    } else {
                        formData[field.dataset.name].value.push(value);
                    }
                } else {
                    formData[field.dataset.name].value = value;
                }
            }
        });

        var url = '/api/project/' + getParam('projectId') + '/product/' + dataset.productId + '/approve';

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            success: this.successChangeStatus.bind(this)
        });
    },
    successChangeStatus: function (data) {
        var table = $('table[data-product-id="' + data.result.id + '"]');
        table[0].style.backgroundColor = 'black';
    }
};