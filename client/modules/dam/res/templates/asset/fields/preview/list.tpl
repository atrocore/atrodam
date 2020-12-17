{{#if icon}}
<a href="?entryPoint=download&id={{fileId}}" target="_blank">
<span class="fiv-cla fiv-icon-{{icon}} fiv-size-lg"></span>
</a>
{{else}}
<a data-action="showImagePreview" data-id="{{get model "id"}}" href="?entryPoint=image&type=asset&size=original&id={{get model "id"}}&v={{timestamp}}">
    <img src="?entryPoint=image&type=asset&size=small&id={{get model "id"}}&v={{timestamp}}" style="max-width: 100px;"/>
</a>
{{/if}}