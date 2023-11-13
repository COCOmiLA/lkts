$(document).ready(function () {
    /** steps changer **/
    var current = 1,
        current_step,
        next_step,
        steps;

    if (Cookies.get('curstep') !== undefined) {
        current = Cookies.get('curstep');
    }

    steps = $(".step-container .steps").length;

    if (document.getElementById('requirements').className != "alert alert-danger") {
        document.getElementById('button_requirements').classList.remove('disabled');
    }

    var button_webserver = true;
    var webserver_alert = document.getElementById('webserver-alert-success');

    if (webserver_alert != null && webserver_alert.style.display == "block") {
        button_webserver = false;
    }

    var webserver_table = document.getElementById('webserver-table');

    if (webserver_table != null) {
        var webserver_table_children = webserver_table.children[0].children;
        var webserver_rows = webserver_table_children.length;

        for (var i = 0; i < webserver_rows; i++) {
            if (webserver_table_children[i].className == "danger") {
                button_webserver = false;
            }
        }
    }

    if (button_webserver) {
        $("#button_webserver").removeClass('disabled');
    }

    $("#environment-form").submit(function () {
        var str = $("#OdinWeb").val();

        if (str.indexOf('http://') === -1 && str.indexOf('https://') === -1) {
            $('#OdinWeb').val('http://' + str);
        }
    });
    $("#omit_dictionary").change(function () {
        if ($("#omit_dictionary").prop('checked')) {
            $("#finish-setup").removeClass('disabled');
            $("#finish-setup").removeAttr('disabled');
        } else {
            $("#finish-setup").addClass('disabled');
            $("#finish-setup").attr('disabled', 'disabled');
        }
    });
    $(".next").click(function () {
        switch (parseInt(current)) {
            case 1:
                if (document.getElementById('button_requirements').className.search('disabled') != -1) {
                    return;
                }

                break;

            case 2:
                if (document.getElementById('button_webserver').className.search('disabled') != -1) {
                    return;
                }

                break;

            case 3:
                if (document.getElementById('button_database').className.search('disabled') != -1) {
                    return;
                }

                $("#migrations-form").trigger('submit');
                break;

            case 4:
                if (document.getElementById('button_migrations').className.search('disabled') != -1) {
                    return;
                }

                break;

            case 5:
                if (document.getElementById('button_environment').className.search('disabled') != -1) {
                    return;
                }

                break;

            case 6:
                if (document.getElementById('finish-setup').className.search('disabled') != -1) {
                    return;
                }

                break;
        }

        current_step = $(this).parent().parent();
        next_step = $(this).parent().parent().next();
        next_step.show();
        current_step.hide();
        setProgressBar(++current);
    });
    $(".previous").click(function () {
        current_step = $(this).parent().parent();
        next_step = $(this).parent().parent().prev();
        next_step.show();
        current_step.hide();
        setProgressBar(--current);
    });
    setProgressBar(current);

    // Change progress bar action
    function setProgressBar(curStep) {
        Cookies.set('curstep', curStep);
        var percent = parseFloat(100 / steps) * curStep;
        percent = percent.toFixed();
        $(".main-progress-bar").css("width", percent + "%").html(percent + "%");
    }

    function goToStep(step) {
        if (step === undefined) {
            return false;
        }

        $(".step-container .steps").hide();
        var el_num = parseInt(step) - 1;
        $(".step-container .steps:eq(" + el_num + ")").show();
        setProgressBar(step);
    }

    function applyMigrations() {
        $('#migrations-loading-indicator').show();
        $.post('/frontend/web/install.php?r=migrations/setup', $(this).serialize()).done(function (result) {
            Cookies.set('migrations-applied', 1);
            $('#migrations-loading-indicator').hide();
            $(".migrations-callback-status .alert-danger").hide();
            $('.migrations-callback-status .alert-success').html(result);
            $(".migrations-callback-status .alert-success").show();
            $("#button_migrations").removeClass('disabled');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            Cookies.set('migrations-applied', 0);
            $('#migrations-loading-indicator').hide();
            $(".migrations-callback-status .alert-success").hide();
            $(".migrations-callback-status .alert-danger").html(jqXHR.responseText);
            $(".migrations-callback-status .alert-danger").show();
            $("#button_migrations").addClass('disabled');
        });
    }

    goToStep(Cookies.get('curstep'));
    /** hold some data in cookies **/

    $("input[name=group1]").on("change", function () {
        var id = $(this).val();
        Cookies.set('webserver', id);
        $(".desc").hide();
        $("#" + id).show();
    });
    $("#DbServerAddress").on("change", function () {
        Cookies.set('DbServerAddress', $(this).val());
    });
    $("#DbUserName").on("change", function () {
        Cookies.set('DbUserName', $(this).val());
    });
    $("#DbType").on("change", function () {
        Cookies.set('DbType', $(this).val());
    });
    $("#DbName").on("change", function () {
        Cookies.set('DbName', $(this).val());
    });
    $("#WebAddress").on("change", function () {
        Cookies.set('WebAddress', $(this).val());
    });
    $("#WebName").on("change", function () {
        Cookies.set('WebName', $(this).val());
    });
    $("#WebAdminEmail").on("change", function () {
        Cookies.set('WebAdminEmail', $(this).val());
    });
    $("#WebOutEmail").on("change", function () {
        Cookies.set('WebOutEmail', $(this).val());
    });
    $("#MailHost").on("change", function () {
        Cookies.set('MailHost', $(this).val());
    });
    $("#MailPort").on("change", function () {
        Cookies.set('MailPort', $(this).val());
    });
    $("#MailProtocol").on("change", function () {
        Cookies.set('MailProtocol', $(this).val());
    });
    $("#MailUsername").on("change", function () {
        Cookies.set('MailUsername', $(this).val());
    });
    $("#OdinName").on("change", function () {
        Cookies.set('OdinName', $(this).val());
    });
    $("#OdinWeb").on("change", function () {
        Cookies.set('OdinWeb', $(this).val());
    });
    $("#phppath").on("change", function () {
        Cookies.set('phppath', $(this).val());
    });
    /** submit php.exe path **/

    $("#phppath-form").submit(function (e) {
        var phppath = $("#phppath").val();
        Cookies.set('phppath', phppath);
        e.preventDefault();
    });
    /** submit webserver configuration **/

    $("#webserver-form").submit(function (e) {
        $('#commonServerErrorsList').empty();

        $.post('/frontend/web/install.php?r=webserver/setup', $(this).serialize()).done(function (xhr) {
            $("#Apache_ServerErrors").hide();
            $("#IIS_ServerErrors").hide();
            $("#noServerErrors").hide();
            $("#commonServerErrors").hide();
            $(".webserver-requirements-total div").hide();
            var selected_server = Cookies.get('webserver');

            if (selected_server !== 'other') {
                $(".webserver-callback-status .alert-success").show();
                $(".webserver-requirements-total .alert-success").show();
            } else {
                $(".webserver-callback-status .alert-success").hide();
                $(".webserver-requirements-total .alert-warning").show();
            }

            Cookies.set('webserver-install', 1);
            $("#button_webserver").removeClass('disabled');
        }).fail(function (xhr) {
            var status = xhr.status;
            $(".webserver-requirements-total div").hide();
            $(".webserver-callback-status .alert-success").hide();

            if (status === 401) {
                $("#noServerErrors").hide();
                $("#IIS_ServerErrors").hide();
                $("#Apache_ServerErrors").show();
            } else if (status === 402) {
                $("#noServerErrors").hide();
                $("#IIS_ServerErrors").show();
                $("#Apache_ServerErrors").hide();
            } else {
                $("#noServerErrors").show();
                $("#IIS_ServerErrors").hide();
                $("#Apache_ServerErrors").hide();
            }

            if (xhr.responseText) {
                $("#commonServerErrors").show();
                $("#commonServerErrorsList").show().append("<li>" + xhr.responseText + "</li>");
            }

            $(".webserver-requirements-total .alert-danger").show();
            Cookies.set('webserver-install', 0);
            $("#button_webserver").addClass('disabled');
        });
        e.preventDefault();
    });
    /** submit database configuration **/

    $("#database-form").submit(function (e) {
        var dbaddress = $("#DbServerAddress").val();
        Cookies.set('DbServerAddress', dbaddress);
        var username = $("#DbUserName").val();
        Cookies.set('DbUserName', username);
        var dbtype = $("#DbType").val();
        Cookies.set('DbType', dbtype);
        var dbname = $("#DbName").val();
        Cookies.set('DbName', dbname);
        $('#db-loading-indicator').show();
        $.post('/frontend/web/install.php?r=database/setup', $(this).serialize()).done(function () {
            $('#db-loading-indicator').hide();
            $(".database-requirements-total div").hide();
            $(".database-callback-status .alert-danger").hide();
            $(".database-callback-status .alert-success").show();
            $(".database-requirements-total .alert-success").show();
            Cookies.set('database-install', 1);
            localStorage.removeItem('database-error');
            $("#button_database").removeClass('disabled');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#db-loading-indicator').hide();
            $(".database-requirements-total div").hide();
            $(".database-callback-status .alert-success").hide();
            $(".database-callback-status .alert-danger").html(jqXHR.responseText);
            $(".database-callback-status .alert-danger").show();
            $(".database-requirements-total .alert-danger").html(jqXHR.responseText);
            $(".database-requirements-total .alert-danger").show();
            Cookies.set('database-install', 0);
            localStorage.setItem('database-error', jqXHR.responseText);
            $("#button_database").addClass('disabled');
        });
        e.preventDefault();
    });
    /** submit migrations **/

    $("#migrations-form").submit(function (e) {
        applyMigrations();
        e.preventDefault();
    });
    /** submit environment configuration **/

    $("#environment-form").submit(function (e) {
        $('#environment-loading-indicator').show();
        $.post('/frontend/web/install.php?r=environment/setup', $(this).serialize()).done(function () {
            $('#environment-loading-indicator').hide();
            $(".environment-requirements-total div").hide();
            $(".environment-callback-status .alert-danger").hide();
            $(".environment-callback-status .alert-success").show();
            $(".environment-requirements-total .alert-success").show();
            Cookies.set('environment-install', 1);
            localStorage.removeItem('environment-error');
            document.getElementById('button_environment').classList.remove('disabled');
        }).fail(function (jqXHR, textStatus, errorThrown) {
            $('#environment-loading-indicator').hide();
            $(".environment-requirements-total div").hide();
            $(".environment-callback-status .alert-success").hide();
            $(".environment-callback-status .alert-danger").html(jqXHR.responseText);
            $(".environment-callback-status .alert-danger").show();
            $(".environment-requirements-total .alert-danger").html(jqXHR.responseText);
            $(".environment-requirements-total .alert-danger").show();
            Cookies.set('environment-install', 0);
            localStorage.setItem('environment-error', jqXHR.responseText);
            $("#button_environment").addClass('disabled');
        });
        e.preventDefault();
    });
    /** submit dictionary configuration **/

    $("#dictionary-form").submit(function (e) {
        // $('#dictionary-loading-indicator').show();
        // $.post('/frontend/web/install.php?r=dictionary/setup', $(this).serialize()).done(function(response) {
        //     $('#dictionary-loading-indicator').hide();
        //     if ($(".dictionary-callback-status").hasClass('alert alert-info')) {
        //         $(".dictionary-callback-status").removeClass('alert alert-info')
        //     }
        //     $(".dictionary-callback-status").html(response);
        //     Cookies.set('dictionary-install', 1);
        //     localStorage.setItem('dictionary-callback', response);
        //     $("#finish-setup").removeClass('disabled');
        //     $('#dictionary-checkbox').hide();
        //     $('#dictionary-checkbox-note').hide();
        // })
        //     .fail(function(jqXHR, textStatus, errorThrown) {
        //         $('#dictionary-loading-indicator').hide();
        //         if ($(".dictionary-callback-status").hasClass('alert alert-info')) {
        //             $(".dictionary-callback-status").removeClass('alert alert-info')
        //         }
        //         $(".dictionary-callback-status").html(jqXHR.responseText);
        //         Cookies.set('dictionary-install', 0);
        //         localStorage.setItem('dictionary-callback', jqXHR.responseText);
        //         $("#finish-setup").addClass('disabled');
        //         $('#dictionary-checkbox').show();
        //         $('#dictionary-checkbox-note').show();
        //     });
        e.preventDefault();
    });
    /** remove setup **/

    $("#finish-setup").click(function (e) {
        e.preventDefault();
        var cookies = ['curstep', 'webserver', 'DbServerAddress', 'DbUserName', 'DbType', 'DbName', 'WebAddress', 'WebName', 'WebAdminEmail', 'WebOutEmail', 'MailHost', 'MailPort', 'MailProtocol', 'MailUsername', 'OdinName', 'OdinWeb', 'webserver-install', 'DbServerAddress', 'DbUserName', 'DbName', 'database-install', 'environment-install', 'dictionary-install', 'migrations-applied'];

        for (var cookie_idx = 0; cookie_idx < cookies.length; cookie_idx++) {
            Cookies.remove(cookies[cookie_idx]);
        }

        localStorage.clear();
        window.location = this.href;
    });

    /** load saved state **/

    function loadData() {
        var webserver = Cookies.get('webserver');

        if (webserver !== undefined) {
            $("input[name=group1][value=" + webserver + "]").prop('checked', true);
            $(".desc").hide();
            $("#" + webserver).show();
        }

        var webserver_setup = Cookies.get('webserver-install');
        $(".webserver-requirements-total div").hide();

        if (webserver_setup === '1') {
            $(".webserver-callback-status .alert-danger").hide();

            if (webserver !== 'other') {
                $(".webserver-requirements-total .alert-success").show();
                $(".webserver-callback-status .alert-success").show();
                $("#button_webserver").removeClass('disabled');
            } else {
                $(".webserver-requirements-total .alert-warning").show();
            }
        } else if (webserver_setup === '0') {
            $(".webserver-callback-status .alert-success").hide();
            $(".webserver-requirements-total .alert-danger").show();
            $(".webserver-callback-status .alert-danger").show();
            $("#button_webserver").addClass('disabled');
        } else {
            $(".webserver-requirements-total .alert-danger").show();
            $("#button_webserver").addClass('disabled');
        }

        var database_setup = Cookies.get('database-install');
        $(".database-requirements-total div").hide();

        if (database_setup === '1') {
            $(".database-callback-status .alert-danger").hide();
            $(".database-callback-status .alert-success").show();
            $(".database-requirements-total .alert-success").show();
            $("#button_database").removeClass('disabled');
        } else if (database_setup === '0') {
            $(".database-callback-status .alert-success").hide();

            if (localStorage.getItem('database-error') !== null) {
                $(".database-callback-status .alert-danger").html(localStorage.getItem('database-error'));
                $(".database-callback-status .alert-danger").show();
            }

            if (localStorage.getItem('database-error') !== null) {
                $(".database-requirements-total .alert-danger").html(localStorage.getItem('database-error'));
            }

            $(".database-requirements-total .alert-danger").show();
            $("#button_database").addClass('disabled');
        } else {
            $(".database-requirements-total .alert-danger").show();
            $("#button_database").addClass('disabled');
        }

        var migrations_applied = Cookies.get('migrations-applied');

        if (migrations_applied === '1') {
            $('#migrations-form-submit').hide();
            $(".migrations-callback-status .alert-danger").hide();
            $(".migrations-callback-status .alert-success").show();
            $("#button_migrations").removeClass('disabled');
        } else if (migrations_applied === '0') {
            $('#migrations-form-submit').show();
            $("#button_migrations").addClass('disabled');
        } else {
            $('#migrations-form-submit').show();
            $("#button_migrations").addClass('disabled');
        }

        var dbaddress = Cookies.get('DbServerAddress');

        if (dbaddress !== undefined) {
            $("#DbServerAddress").val(dbaddress);
        }

        var username = Cookies.get('DbUserName');

        if (username !== undefined) {
            $("#DbUserName").val(username);
        }

        var dbtype = Cookies.get('DbType');

        if (dbtype !== undefined) {
            $("#DbType").val(dbtype);
        }

        var dbname = Cookies.get('DbName');

        if (dbname !== undefined) {
            $("#DbName").val(dbname);
        }

        var WebAddress = Cookies.get('WebAddress');

        if (WebAddress !== undefined) {
            $("#WebAddress").val(WebAddress);
        }

        var WebName = Cookies.get('WebName');

        if (WebName !== undefined) {
            $("#WebName").val(WebName);
        }

        var WebAdminEmail = Cookies.get('WebAdminEmail');

        if (WebAdminEmail !== undefined) {
            $("#WebAdminEmail").val(WebAdminEmail);
        }

        var WebOutEmail = Cookies.get('WebOutEmail');

        if (WebOutEmail !== undefined) {
            $("#WebOutEmail").val(WebOutEmail);
        }

        var MailHost = Cookies.get('MailHost');

        if (MailHost !== undefined) {
            $("#MailHost").val(MailHost);
        }

        var MailPort = Cookies.get('MailPort');

        if (MailPort !== undefined) {
            $("#MailPort").val(MailPort);
        }

        var MailProtocol = Cookies.get('MailProtocol');

        if (MailProtocol !== undefined) {
            $("#MailProtocol").val(MailProtocol);
        }

        var MailUsername = Cookies.get('MailUsername');

        if (MailUsername !== undefined) {
            $("#MailUsername").val(MailUsername);
        }

        var OdinName = Cookies.get('OdinName');

        if (OdinName !== undefined) {
            $("#OdinName").val(OdinName);
        }

        var OdinWeb = Cookies.get('OdinWeb');

        if (OdinWeb !== undefined) {
            $("#OdinWeb").val(OdinWeb);
        }

        var environment_setup = Cookies.get('environment-install');
        $(".environment-requirements-total div").hide();

        if (environment_setup === '1') {
            $(".environment-callback-status .alert-danger").hide();
            $(".environment-callback-status .alert-success").show();
            $(".environment-requirements-total .alert-success").show();
            document.getElementById('button_environment').classList.remove('disabled');
        } else if (environment_setup === '0') {
            $(".environment-callback-status .alert-success").hide();

            if (localStorage.getItem('environment-error') !== null) {
                $(".environment-callback-status .alert-danger").html(localStorage.getItem('environment-error'));
                $(".environment-callback-status .alert-danger").show();
            }

            if (localStorage.getItem('environment-error') !== null) {
                $(".environment-requirements-total .alert-danger").html(localStorage.getItem('environment-error'));
            }

            $(".environment-requirements-total .alert-danger").show();
            $("#button_environment").addClass('disabled');
        } else {
            $(".environment-requirements-total .alert-danger").show();
            $("#button_environment").addClass('disabled');
        }

        checkErrors();
    }

    function checkErrors() {
        var text = localStorage.getItem('dictionary-callback');

        if (text) {
            $(".dictionary-callback-status").show();
        } else {
            $(".dictionary-callback-status").hide();
        }

        if (text) {
            $('#dictionary-checkbox').show();
            $('#dictionary-checkbox-note').show();
            $("#finish-setup").addClass('disabled');
            $("#finish-setup").attr('disabled', 'disabled');
        } else {
            $('#dictionary-checkbox').hide();
            $('#dictionary-checkbox-note').hide();
        }
    }

    loadData();

    // обновление справочников
    var doneMethods = 0;
    var progressStep = 0;
    var dictionariesArray = [];
    var dictionariesArrayKeys = [];
    var warnings = [];

    $("#dictionary-button").click(function () {
        resetProgress();
        showUpdateRow();
        $("#finish-setup").addClass('disabled');
        $("#finish-setup").attr('disabled', 'disabled');
        $.ajax({
            url: '/frontend/web/install.php?r=dictionary/getDictsList',
            type: 'get'
        }).done(function (res) {
            progressStep = Math.round(70 / Object.keys(res).length);
            dictionariesArray = res;
            dictionariesArrayKeys = Object.keys(dictionariesArray);
            updateDictionary();
        }).catch(function (res) {
            console.error(res);
        });
    });

    function updateOneDictionary(index) {
        if (dictionariesArrayKeys.length === index) {
            makeRequest('linkRefODataFields', 'Связь с элементами справочников');
            finishProgressBar();
            setCurrentDictionary('Готово!');
            showTextSuccess('Все справочники успешно установлены.');
            $("#finish-setup").removeClass('disabled');
            $("#finish-setup").removeAttr('disabled');
            return true;
        }

        var method = dictionariesArrayKeys[index];
        setCurrentDictionary(dictionariesArray[method] || method);
        return fetchMethod(method).done(function (res) {
            if (res && res.status) {
                increaseDoneMethods();
                updateOneDictionary(index + 1);
            } else {
                showTextError(res.error_message);
            }
            checkErrors();
        }).catch(function (e) {
            if (processErrorResponse(method, e)) {
                increaseDoneMethods();
                updateOneDictionary(index + 1);
            }
        });
    }

    function updateDictionary() {
        updateOneDictionary(0);
    }

    function showUpdateRow() {
        $("#update-wrapper").show();
    }

    function increaseDoneMethods() {
        doneMethods += 1;
        var progress = doneMethods * progressStep;
        $('#progress-bar').width(progress + '%');
    }

    function resetProgress() {
        doneMethods = 0;
        progressStep = 0;
        $('#progress-bar').width(0); // Clear Warnings

        warnings = [];
        redrawWarning(); // Clear Errors

        $("#alert-wrapper").hide();
        $("#alert-success-wrapper").hide();
    }

    function setCurrentDictionary(dictionaryName) {
        $("#progress-label").html(dictionaryName);
    }

    function finishProgressBar() {
        $('#progress-bar').width(100 + "%");
    }

    function showTextError(err) {
        $("#alert-wrapper").show();
        $("#dictionary-update-error").html(err);
        var prev = localStorage.getItem('dictionary-callback');
        localStorage.setItem('dictionary-callback', prev + err);
    }

    function showTextSuccess(text) {
        $("#alert-success-wrapper").show();
        $("#dictionary-update-success").html(text);
        localStorage.removeItem('dictionary-callback');
    }

    function appendWarning(dictionary, warning) {
        warnings.push({
            dictionary: dictionary,
            warning: warning
        });
        redrawWarning();
    }

    function redrawWarning() {
        // vue.js is better than this
        var wrapper = $("#alert-warning-wrapper");
        wrapper.show();
        wrapper.html("");

        for (var warning_idx = 0; warning_idx < warnings.length; warning_idx++) {
            var warning = warnings[warning_idx];
            var alert = document.createElement("div");
            alert.classList.add("alert");
            alert.classList.add("alert-warning");
            var span = document.createElement("span");
            span.innerHTML = '<b>' + warning.dictionary + '</b>: ' + warning.warning;
            alert.appendChild(span);
            wrapper.append(alert);
        }
    }

    function fetchMethod(method) {
        return $.ajax({
            url: '/frontend/web/install.php?r=dictionary/updateOneDictionary',
            data: {
                'method': method
            },
            type: 'get'
        });
    }

    function processErrorResponse(method, e) {
        var message = '';

        if (e.responseJSON && e.responseJSON.message && e.responseJSON.message === 'no-data-warning') {
            appendWarning(dictionariesArray[method], 'Нет данных');
            return true;
        }
        if (e.responseText && e.responseText.includes('no-data-warning')) {
            appendWarning(dictionariesArray[method], 'Нет данных');
            return true;
        }

        console.error(e);

        if (e.responseJSON && e.responseJSON.message) {
            message = e.responseJSON.message;

            if (e.responseJSON['stack-trace']) {
                try {
                    message = message + "<br><br>" + e.responseJSON['stack-trace'].join("<br>");
                } catch (er) {
                    console.error(er);
                }
            }
        } else if (e.responseText) {
            message = e.responseText;
        } else {
            message = 'Внутренняя ошибка сервера';
        }

        showTextError(message);
        checkErrors();
        return false;
    }

    function makeRequest(method, methodName) {
        setCurrentDictionary(methodName || dictionariesArray[method]);

        try {
            fetchMethod(method).done(function (res) {
                if (res && res.error_message) {
                    showTextError(res.error_message);
                }
                checkErrors();
                return res && res.status;
            });
        } catch (e) {
            return processErrorResponse(method, e);
        }
    }
});