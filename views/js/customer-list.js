(function() {
    function getCustomerId(el) {
        const text = $(el).parent().parent().html();
        var regex = /id_customer\=(\d+)\&/gi;
        match = regex.exec(text);
        return match[1];
    }

    if (window.LOGIN_AS_URL) {
        $(document).ready(function () {
            var parts = window.location.search.substr(1).split("&");
            var $_GET = {};
            for (var i = 0; i < parts.length; i++) {
                var temp = parts[i].split("=");
                $_GET[decodeURIComponent(temp[0])] = decodeURIComponent(temp[1]);
            }
            if ($_GET.controller == "AdminCustomers") {
                $('.table.customer').find('tr').each(function () {
                    $(this).find('td:last-child').before('<td><a class="login-as-trigger btn btn-default _blank"><i class="icon-user"></i></a></td>');
                    $(this).find('th:last-child').before('<th>&nbsp;</th>');
                });
                $('.login-as-trigger').click(function () {
                    window.open(window.LOGIN_AS_URL + '&id_customer=' + getCustomerId(this), '_blank');
                });
            }
        });
    }
})();
