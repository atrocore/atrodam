{{#if blocks.length}}
    <div class="group-container asset-relation-container">
        {{#each blocks}}
            <div class="group" data-name="{{this}}">{{{var this ../this}}}</div>
        {{/each}}
    </div>
{{else}}
    <div class="list-container">{{translate 'No Data'}}</div>
{{/if}}

<style>
    .bottom .panel .group-container > .group:not(:last-child) {
        margin-bottom: 0;
    }
</style>
