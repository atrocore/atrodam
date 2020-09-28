<table class="table full-table">
    {{#each items}}
    <tr data-name="{{this}}" class="attachment-item">
        {{{var this ../this}}}
    </tr>
    {{/each}}
</table>

<style>
    .attachment-item .overview {
        min-height: 1px !important;
    }
</style>