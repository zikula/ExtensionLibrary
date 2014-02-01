{include file='User/header.tpl'}
<h3>{gt text="Documentation"}</h3>
<div>
    {if $json}<pre><code>{/if}
    {$docfile}
    {if $json}</code></pre>{/if}
</div>