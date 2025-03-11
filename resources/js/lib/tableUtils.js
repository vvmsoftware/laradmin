export function formatUser(user) {
    return user.name + " <" + user.email + ">";
}

export function onSort(sortBy, sortDir) {
    this.filters.sortBy = sortBy;
    this.filters.sortDir = sortDir;
    this.getData();
}

export function onPageChange(page) {
    this.filters.page = page.page;
    this.getData();
}

export function onSearch(search) {
    this.filters.page = 1;
    this.filters.search = search;
    this.getData();
}

export function onLimit(perPage) {
    // Save the perPage to local browser storage
    localStorage.setItem('table-per-page', perPage);
    this.filters.page = 1;
    this.filters.perPage = perPage;
    this.getData();
}

export function formatRules() {
    const rules = [];
    Object.keys(this.columns).forEach((key) => {
        rules.push(this.columns[key]);
    });
    return rules;
}

export function onApplyFilters() {
    this.filters.page = 1;
    this.filters.filters = JSON.stringify(this.advancedFilters);
    this.getData();
}

export function onFilters(filters) {
    this.advancedFilters = filters;
}