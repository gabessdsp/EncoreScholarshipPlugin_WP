/*jslint
    for, browser, multivar, single, white
*/
/*property
    _wpnonce, action, add, ajax_url, append, attr, camps, contains,
    counselornonce, data, done, each, email, empty, fail, find, focus, grade,
    grades, hide, inputs, is, join, length, li, name, nontheatre, on, parent,
    post, prepend, preventDefault, productions, push, ready, remove,
    scholarships, search, selected, show, span, splice, split, staffnonce,
    stopPropagation, table, target, test, text, trim, val
*/
/*global
    wordpress, jQuery
*/

(function ($) {
    "use strict";
    $(document).ready(function () {
        var stafftimeout = null,
            staffinprogress = false,
            counselortimeout = null,
            counselorinprogress = false,
            textarea = $("#sch_application_staff"),
            textarea2 = $("#hiddenCounselor"),
            staffsearch = $("#sch_application_newstaff"),
            counselorsearch = $("#counselor"),
            list = $("#sch_application_staff_list"),
            list2 = $("#sch_application_counselor_list"),
            staffcompletions = $("#sch_application_staff_completions"),
            counselorcompletions = $("#sch_application_counselor_completions"),
            staff = [],
            counselors = [],
            mailre = /.+@.+\..+/, // overly simple, but address is validated serverside. declared here so it is only compiled once
            rowcount = -1,
            tables = {
                nontheatre: {
                    inputs: [
                        {name: "name", span: 6}
                    ],
                    grades: true
                },
                productions: {
                    inputs: [
                        {name: "show", span: 2},
                        {name: "location", span: 2},
                        {name: "character", span: 2}
                    ],
                    grades: true
                },
                camps: {
                    inputs: [
                        {name: "name", span: 6}
                    ],
                    grades: true
                },
                scholarships: {
                    inputs: [
                        {name: "name", span: 5},
                        {name: "amount", span: 2}
                    ],
                    grades: false
                }
            },
            // http://caniuse.com/#feat=es6-number
            ie_11 = !(Number.hasOwnProperty("isFinite"));

        staff.contains = function (s) {
            var i, l = staff.length;
            for (i = 0; i < l; i += 1) { // old-style loop so function can short-circuit on success
                if (staff[i].email === s.email) {
                    return true;
                }
            }
            return false;
        };
        staff.remove = function (li) {
            var i, l = staff.length;
            for (i = 0; i < l; i += 1) {
                if (li.is(staff[i].li)) {
                    return staff.splice(i, 1);
                }
            }
        };

        counselors.contains = function (s) {
            var i, l = counselors.length;
            for (i = 0; i < l; i += 1) { // old-style loop so function can short-circuit on success
                if (counselors[i].email === s.email) {
                    return true;
                }
            }
            return false;
        };
        counselors.remove = function (li) {
            var i, l = counselors.length;
            for (i = 0; i < l; i += 1) {
                if (li.is(counselors[i].li)) {
                    return counselors.splice(i, 1);
                }
            }
        };

        function listitem(s) {
            return $("<li></li>")
                .append($("<div></div>")
                    .text(s.name + " ")
                    .append($("<em></em>")
                        .text(s.email))
                    .prepend($("<span></span>")
                        .append($('<a class="ntdelbutton">X</a>')
                            .on("click", function () {
                                staff.remove(s.li);
                                s.li.remove();
                            }))));
        }

        function listitem2(s) {
            return $("<li></li>")
                .append($("<div></div>")
                    .text(s.name + " ")
                    .append($("<em></em>")
                        .text(s.email))
                    .prepend($("<span></span>")
                        .append($('<a class="ntdelbutton">X</a>')
                            .on("click", function () {
                                counselors.remove(s.li);
                                s.li.remove();
                            }))));
        }

        function removerow(event) {
            event.preventDefault();
            $(event.target).parent().parent().remove();
        }
        function gradeclick(event) {
            event.preventDefault();
            event.stopPropagation();
            event.target.selected = !(event.target.selected);
        }
        function gradeoptions(grades, def) {
            var options = $();
            $.each(grades, function (ignore, grade) {
                var option = $("<option></option>")
                    .val(grade)
                    .text(grade)
                    .append($("<sup>th</sup>"));
                if (ie_11) {
                    option.attr("title", "Hold ctrl to select multiple grades.");
                } else {
                    option.on("mousedown", gradeclick);
                }
                if (grade === def) {
                    option.attr("selected", "selected");
                }
                options = options.add(option);
            });
            return options;
        }
        function tablerow(slug, activity) {
            var tr = $("<tr></tr>");
            rowcount += 1; // This is never decremented. That is intended, as all that matters is that the indicies are unique.
            $.each(tables[slug].inputs, function (ignore, input) {
                tr.append($("<td></td>")
                    .text((input.name === "amount") ? "$ " : "")
                    .attr("colspan", input.span)
                    .append($('<input>')
                        .attr("type", (input.name === "amount") ? "number" : "text")
                        .attr("name", slug + "[" + input.name + "][" + String(rowcount) + "]")
                        .val(activity !== undefined
                            ? activity[input.name]
                            : "")));
            });
            if (tables[slug].grades) {
                tr.append($("<td></td>")
                    .append($('<select multiple size="2" name="' + slug + '[grades][' + String(rowcount) + '][]">')
                        .append(gradeoptions(["9", "10", "11", "12"], activity !== undefined
                            ? activity.grade
                            : 0))));
                        //    : 0))
                        //.on("mousedown", function (event) {
                        //    event.preventDefault();
                        //})));
            }
            tr.append($("<td></td>")
                .append($('<a href="#">Remove</a>')
                    .on("click", removerow)));
            return tr;
        }

        console.log("textarea value");
        console.log(textarea.val());

        $.each(textarea.val().split(","), function (index, email) {
            var member;
            email = email.trim();
            if (email === "") {
                return;
            }
            member = {name: textarea.data("names").split(",")[index].trim(), email: email};
            member.li = listitem(member);
            staff.push(member);
            list.append(member.li);
            console.log("LISTING A STAFF MEMBER");
        });

        console.log("textarea2 value");
        console.log(textarea2.val());

        $.each(textarea2.val().split(","), function (index, email) {
            console.log("TRYING TO LIST A COUNSELOR");
            var member2;
            email = email.trim();
            if (email === "") {
                return;
            }

            console.log(textarea2.data("names"));

            // member2 = {name: textarea2.data("names").trim(), email: email};//problematic trim
            member2 = {name: textarea2.data("names"), email: email};
            member2.li = listitem2(member2);
            counselors.push(member2);
            list2.append(member2.li);
            console.log("LISTING A COUNSELOR");
        });

        staffsearch.on("input", function () {
            var val = staffsearch.val().trim();
            staffcompletions.hide();
            if (staffinprogress) {
                return;
            }
            if (stafftimeout !== null) {
                clearTimeout(stafftimeout);
            }
            if (val === "") {
                return;
            }
            stafftimeout = setTimeout(function () {
                staffinprogress = true;
                stafftimeout = null;
                staffcompletions.empty();
                staffcompletions.append($('<li><em style="float: none;">Loading...</em></li>'));
                staffcompletions.show();
                $.post(
                    wordpress.ajax_url,
                    {
                        action: "sch_application_search_staff",
                        search: val,
                        "_wpnonce": wordpress.staffnonce
                    },
                    null,
                    "json"
               ).done(function (data) {
                    var n = 0, match = false;
                    staffinprogress = false;
                    staffcompletions.empty();

                    function completion(s) {
                        return $("<li></li>")
                            .text(s.name + " ")
                            .append($("<em></em>")
                                .text(s.email))
                            .on("click", function () {
                                if (!staff.contains(s)) {
                                    staff.push(s);
                                    s.li = listitem(s);
                                    list.append(s.li);
                                }
                            });
                    }

                    if (data.length === 0) {
                        if (mailre.test(val)) {
                            staffcompletions.append(completion({name: "(Invite staff)", email: val}));
                        }
                        staffcompletions.append($('<li><em style="float: none;">No results.</em></li>'));
                        return;
                    }
                    $.each(data, function (ignore, s) {
                        match = match || s.email === val;
                        if (!staff.contains(s)) {
                            n += 1;
                            staffcompletions.append(completion(s));
                        }
                    });
                    if (!match && mailre.test(val)) {
                        staffcompletions.append(completion({name: "(Invite staff)", email: val}));
                    }
                    if (n === 0) {
                        staffcompletions.append($('<li><em style="float: none;">No more results.</em></li>'));
                    }
                }).fail(function () {
                    staffinprogress = false;
                    staffcompletions.empty();
                    staffcompletions.append('<li><em style="float: none;">An unknown error occurred. Try again!</em></li>');
                });
            }, 500);
        });
        counselorsearch.on("input", function () {
            var val = counselorsearch.val().trim();
            counselorcompletions.hide();
            if (counselorinprogress) {
                return;
            }
            if (counselortimeout !== null) {
                clearTimeout(counselortimeout);
            }
            if (val === "") {
                return;
            }
            counselortimeout = setTimeout(function () {
                counselorinprogress = true;
                counselortimeout = null;
                counselorcompletions.empty();
                counselorcompletions.append($('<li><em style="float: none;">Loading...</em></li>'));
                counselorcompletions.show();
                $.post(
                    wordpress.ajax_url,
                    {
                        action: "sch_application_search_counselors",
                        search: val,
                        "_wpnonce": wordpress.counselornonce
                    },
                    null,
                    "json"
               ).done(function (data) {
                    var n = 0, match = false;
                    counselorinprogress = false;
                    counselorcompletions.empty();

                    function completion(counselor) {
                        return $("<li></li>")
                            .text(counselor.name + " ")
                            .append($("<em></em>")
                                .text(counselor.email))
                            .on("click", function () {
                                if (!counselors.contains(counselor)) {
                                    counselors.push(counselor);
                                    counselor.li = listitem2(counselor);
                                    list2.append(counselor.li);
                                }
                            });
                    }

                    if (data.length === 0) {
                        if (mailre.test(val)) {
                            counselorcompletions.append(completion({name: "(Invite counselor)", email: val}));
                        }
                        counselorcompletions.append($('<li><em style="float: none;">No results.</em></li>'));
                        return;
                    }
                    $.each(data, function (ignore, counselor) {
                        match = match || counselor.email === val;
                        if (!staff.contains(counselor)) {
                            n += 1;
                            counselorcompletions.append(completion(counselor));
                        }
                    });
                    if (!match && mailre.test(val)) {
                        counselorcompletions.append(completion({name: "(Invite counselor)", email: val}));
                    }
                    if (n === 0) {
                        counselorcompletions.append($('<li><em style="float: none;">No more results.</em></li>'));
                    }
                }).fail(function () {
                    counselorinprogress = false;
                    counselorcompletions.empty();
                    counselorcompletions.append('<li><em style="float: none;">An unknown error occurred. Try again!</em></li>');
                });
            }, 500);
        });
        $("#post").on("submit", function () {
            var emails = [];
            $.each(staff, function (ignore, s) {
                emails.push(s.email);
            });
            textarea.val(emails.join(","));

            var counselorEmail = [];
            console.log('GABE');
            console.log(counselors);
            console.log(counselors[0]);
            $.each(counselors, function (ignore, s) {
                counselorEmail.push(s.email);
            });
            // textarea2.val(counselorEmail.join(","));
            textarea2.val(counselorEmail);
        });
        staffcompletions.hide();
        counselorcompletions.hide();

        $.each(tables, function (slug, data) {
            data.table = $("#sch_application_" + slug);
            data.table.find(".sch_application_add").on("click", function (e) {
                e.preventDefault();
                data.table.append(tablerow(slug));
            });
            if (ie_11) {
                data.table.find("option").attr("title", "Hold ctrl to select multiple grades.");
            } else {
                data.table.find("option").on("mousedown", gradeclick);
            }
        });

        $(".sch_application_activitytable").find("tbody").find("a").each(function (ignore, a) {
            $(a).on("click", removerow);
            rowcount += 1;
        });
    });
}(jQuery));