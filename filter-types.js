/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

import MultiSelect from "./filter-types/multi-select.js";
import DateFilter from "./filter-types/date-filter.js";
import DateRange from "./filter-types/date-range.js";
import SizeCurrency from "./filter-types/size-currency.js";
import { potentialResultsAjax } from "./potential-results.js";

export default class FilterTypes {
    constructor() {
        this.autocompleteTypes = [
            "fund",
            "fund_manager",
            "manager",
            "consultant",
            "investor_manager",
            "mandate_manager",
            "investor_consultant",
            "mandate_institutional_investor",
            "institutional_investor",
            "search_consultant"
        ];
        console.log("class: FilterTypes");
        // Get DOM Elements
        this.elementFilterBodyRows = document.querySelector(".filter-body .filter-rows");
    }

    addFilterRow = (type, name, slug) => {
        switch (type) {
            case "multiselect":
                let multiSelectRow = new MultiSelect(name, slug);
                this.elementFilterBodyRows.insertAdjacentHTML("beforeend", multiSelectRow.render());
                multiSelectRow.populateOptionsData(slug).then(data => {
                    if (this.autocompleteTypes.indexOf(slug) != -1) {
                        // add key up listener
                        this.multiselectAutocomplete(multiSelectRow.ID, slug);
                    } else {
                        this.initializeMultiselectField(multiSelectRow.ID, data);
                    }
                });
                return multiSelectRow.ID;
            case "date":
                let dateFilter = new DateFilter(name, slug);
                this.elementFilterBodyRows.insertAdjacentHTML("beforeend", dateFilter.render());
                dateFilter.populateOptionsData(dateFilter.ID, slug);
                this.changeSelectListener(dateFilter.ID);
                return dateFilter.ID;
            case "date_range":
                let dateRange = new DateRange(name, slug);
                this.elementFilterBodyRows.insertAdjacentHTML("beforeend", dateRange.render());
                this.initializeDateRangeField();
                return dateRange.ID;
            case "size_currency":
                let sizeCurrency = new SizeCurrency(name, slug);
                this.elementFilterBodyRows.insertAdjacentHTML("beforeend", sizeCurrency.render());
                sizeCurrency.populateOptionsData(slug, sizeCurrency.currencyID).then(data => {
                    this.initializeMultiselectField(sizeCurrency.sizeID, data.sizes);
                    this.changeSelectListener("aum-currency-" + sizeCurrency.commonUniquID);
                });
                return sizeCurrency.commonUniquID;
            default:
            // code block
        }
    };

    removeFilterRow = element => {
        element.remove();
        potentialResultsAjax();
    };

    // Method that need to be fired after the render is done and the html is printed
    initializeDateRangeField = () => {
        $(".filter-calendar")
            .datepicker({
                format: "yyyy-mm-dd"
            })
            .on("hide", function() {
                let parent = $(this).parents(".js-range-filter");
                if (parent.find(".js-start-date").val() && parent.find(".js-end-date").val()) {
                    potentialResultsAjax();
                }
            });
    };

    changeSelectListener = id => {
        document.querySelector("#" + id).addEventListener("change", function() {
            potentialResultsAjax();
        });
    };

    // Method that need to be fired after the render is done and the html is printed
    initializeMultiselectField = (id, data) => {
        if (id) {
            $("#" + id + ".js-multi-select2")
                .select2({
                    placeholder: "",
                    data: data
                })
                .on("select2:select", function() {
                    potentialResultsAjax();
                })
                .on("select2:unselect", function() {
                    potentialResultsAjax();
                });
        }
    };

    multiselectAutocomplete = (id, slug) => {
        if (id) {
            $("#" + id + ".js-multi-select2")
                .select2({
                    placeholder: "",
                    ajax: {
                        url: ajaxurl,
                        dataType: "json",
                        data: function(params) {
                            return {
                                action: "get_field_data",
                                field_slug: slug,
                                keyword: params.term
                            };
                        },
                        delay: 600,
                        minimumInputLength: 3,
                        processResults: function(data) {
                            return {
                                results: data
                            };
                        },
                        cache: true
                    }
                })
                .on("select2:select", function() {
                    potentialResultsAjax();
                })
                .on("select2:unselect", function() {
                    potentialResultsAjax();
                });
        }
    };
}
