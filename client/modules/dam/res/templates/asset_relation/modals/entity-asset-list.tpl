<div class="row">
    <div class="col-sm-12 attachment-list">
        <table class="table full-table">
            {{#each items}}
            <tr data-name="{{this}}">
                {{{var this ../this}}}
            </tr>
            {{/each}}
        </table>
    </div>
</div>