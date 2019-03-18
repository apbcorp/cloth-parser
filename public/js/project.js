function init() {
    projectSelector.init();
}

var projectSelector = {
    init: function () {
        this.getProjectList();
    },
    getProjectList: function () {
        $.ajax({
            url: 'api/project/list',
            success: this.successGetProjectList.bind(this)
        })
    },
    successGetProjectList: function(data) {
        this.onShow(data.result);
    },
    onShow: function (data) {
        var selector = document.createElement('select');
        var option = document.createElement('option');
        option.setAttribute('value', 0);
        option.innerText = '';

        selector.appendChild(option);

        for (var key in data) {
            var option = document.createElement('option');
            option.setAttribute('value', key);
            option.innerText = data[key];

            selector.appendChild(option);
        }

        $('body')[0].appendChild(selector);

        $('select').on('change', this.onSelectProject.bind());
    },
    onSelectProject: function (event) {
        document.location.href = '/project/' + event.currentTarget.value + '?key=' + getParam('key') + '&projectId=' + event.currentTarget.value;
    }
};