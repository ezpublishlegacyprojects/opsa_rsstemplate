<form action={"rsstemplate/edit_export"|ezurl} method="post" name="RSSExport">

<div class="context-block">
{* DESIGN: Header START *}<div class="box-header"><div class="box-tc"><div class="box-ml"><div class="box-mr"><div class="box-tl"><div class="box-tr">

<h1 class="context-title">{'Edit <%rss_export_name> [RSS Export]'|i18n( 'design/admin/rss/edit_export',, hash( '%rss_export_name', $rss_export.title ) )|wash}</h1>

{* DESIGN: Mainline *}<div class="header-mainline"></div>

{* DESIGN: Header END *}</div></div></div></div></div></div>

{* DESIGN: Content START *}<div class="box-ml"><div class="box-mr"><div class="box-content">

<div class="context-attributes">

    {section show=not($valid)}
        <div class="warning">
            <h2>{'Invalid input'|i18n( 'design/admin/rss/edit_export' )}</h2>
            <ul>
{section var=Errors loop=$validation_errors}
             <li>{$Errors.item}</li>
{/section}
{*                <li>{'If RSS Export is Active then a valid Access URL is required.'|i18n( 'design/admin/rss/edit_export' )}</li>*}
            </ul>
        </div>
    {/section}

    <div class="block">
    <label>{'Name'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <input class="halfbox" id="exportName" type="text" name="title" value="{$rss_export.title|wash}" title="{'Name of the RSS export. This name is used in the Administration Interface only, to distinguish the different exports from each other.'|i18n('design/admin/rss/edit_export')}" />
    </div>

    <div class="block">
    <label>{'Description'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <textarea class="halfbox" name="Description" rows="3" title="{'Use the description field to write a text explaining what users can expect from the RSS export.'|i18n('design/admin/rss/edit_export')}">{$rss_export.description|wash}</textarea>
    </div>

    <div class="block">
    <label>{'Site URL'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <input class="halfbox" type="text" name="url" value="{$rss_export.url|wash}"/>
    <div class="context-attributes">
        <p>{'Use this field to enter the base URL of your site. It is used to produce the URLs in the export, composed by the Site URL (e.g. "http://www.example.com/index.php") and the path to the object (e.g. "/articles/my_article"). The Site URL depends on your web server and eZ Publish configuration.'|i18n( 'design/admin/rss/edit_export')}</p>
        <p>{'Leave this field empty if you want system automaticaly detect the URL of your site from the URL you access feed with'|i18n( 'design/admin/rss/edit_export')}</p>
    </div>
    </div>

    <input type="hidden" name="RSSImageID" value="{$rss_export.image_id}" />

    <div class="block">
    <label>{'Image'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <input type="text" readonly="readonly" size="45" value="{$rss_export.image_path|wash}" />
    <input class="button" type="submit" name="BrowseImageButton" value="{'Browse'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click this button to select an image for the RSS export. Note that images only work with RSS version 2.0'|i18n('design/admin/rss/edit_export')}" />
    </div>
    {section name=RemoveImage show=eq( $rss_export.image_id, 0 )|not }
      <div class="block">
        <input class="button" type="submit" name="RemoveImageButton" value="{'Remove image'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click to remove image from RSS export.'|i18n('design/admin/rss/edit_export')}" />
      </div>
    {/section}

    <div class="block">
    <label>{'RSS version'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <select name="RSSVersion" title="{'Use this drop-down menu to select the RSS version to use for the export. You must select RSS 2.0 in order to export the image selected above.'|i18n('design/admin/rss/edit_export')}">
    {section name=Version loop=$rss_version_array}
    <option
    {section name=DefaultSet show=eq( $rss_export.rss_version, 0 )}
      {section name=Default show=eq( $Version:item, $rss_version_default )}
        selected="selected"
      {/section}
    {section-else}
      {section name=Default2 show=eq( $Version:item, $rss_export.rss_version )}
        selected="selected"
      {/section}
    {/section}
      value="{$:item}">{$:item|wash}
    </option>
    {/section}
    </select>
    </div>

    <div class="block">
    <label>{'Number of objects'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <select name="NumberOfObjects" title="{'Use this drop-down to select the maximum number of objects included in the RSS feed.'|i18n('design/admin/rss/edit_export')}">
    {section name=Number loop=$number_of_objects_array}
    <option
    {section name=DefaultSet show=eq( $rss_export.number_of_objects, 0 )}
      {section name=Default show=eq( $Number:item, $number_of_objects_default )}
        selected="selected"
      {/section}
    {section-else}
      {section name=Default2 show=eq( $Number:item, $rss_export.number_of_objects )}
        selected="selected"
      {/section}
    {/section}
      value="{$:item}">{$:item|wash}
    </option>
    {/section}
    </select>
    </div>

    <div class="block">
    <label>{'Active'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <input type="checkbox" name="active" {section show=$rss_export.active|eq( 1 )}checked="checked"{/section} title="{'Use this checkbox to control if the RSS export is active or not. An inactive export will not be automatically updated.'|i18n('design/admin/rss/edit_export')}"/>
    </div>

    <div class="block">
    <label>{'Main node only'|i18n( 'design/admin/rss/edit_export' )}:</label>
    <input type="checkbox" name="MainNodeOnly" {section show=$rss_export.main_node_only|eq( 1 )}checked="checked"{/section} title="{'Check if you want to only feed the object from the main node.'|i18n('design/admin/rss/edit_export')}"/>
    </div>

    <div class="block">
    <label>{'Access URL'|i18n( 'design/admin/rss/edit_export' )}:</label>
    rss/feed/<input class="halfbox" type="text" name="Access_URL" value="{$rss_export.access_url|wash}" title="{'Use this field to set the URL where the RSS export should be available. Note that "rss/feed/" will be appended to the real URL. '|i18n('design/admin/rss/edit_export')|wash}" />
    </div>


    <input type="hidden" name="RSSExport_ID" value={$rss_export.id} />
    <input type="hidden" name="Item_Count" value={count($rss_export.item_list)} />

<hr />

    {section name=Source loop=$rss_export.item_list}


       <h2>{'Source'|i18n( 'design/admin/rss/edit_export' )} {sum($Source:index, 1)}</h2>

       <input type="hidden" name="Item_ID_{$Source:index}" value="{$Source:item.id}" />
       <div class="block">
       <label>{'Source path'|i18n( 'design/admin/rss/edit_export' )}:</label>
       <input type="text" readonly="readonly" size="45" value="{$Source:item.source_path|wash}" />
       <input class="button" type="submit" name="{concat( 'SourceBrowse_', $Source:index )}" value="{'Browse'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click this button to select the source node for the RSS export source. Objects of the type selected in the drop-down below published as sub items of the selected node will be included in the RSS export.'|i18n('design/admin/rss/edit_export')}" />
       </div>
       
        <div class="block">
        <label>{'Subnodes'|i18n( 'design/admin/rss/edit_export' )}:</label>
        <input type="checkbox" name="Item_Subnodes_{$Source:index}" {section show=$Source:item.subnodes|wash|eq( 1 )}checked="checked"{/section} title="{'Activate this checkbox if objects from the subnodes of the source should also be fed.'|i18n('design/admin/rss/edit_export')}"/>
        </div>
    
       <div class="block">
       <label>{'Class'|i18n( 'design/admin/rss/edit_export' )}:</label>
       <select name="Item_Class_{$Source:index}" title="{'Use this drop-down to select the type of object that triggers the export. Click the "Set" button to load the correct attribute types for the remaining fields.'|i18n('design/admin/rss/edit_export')|wash}">
       {section name=ContentClass loop=$rss_class_array }
       <option
       {section name=Class show=eq( $:item.id, $Source:item.class_id )}
         selected="selected"
       {/section} value="{$:item.id}">{$:item.name|wash}</option>
       {/section}
       </select>
       <input class="button" type="submit" name="Update_Item_Class" value="{'Set'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click this button to load the correct values into the drop-down fields below. Use the drop-down menu on the left to select the class.'|i18n('design/admin/rss/edit_export')}" />
       </div>

       {section name=Attribute show=count( $rss_export.item_list[$Source:index] )|gt( 0 )}

         <div class="block">
         <label>{'Title'|i18n( 'design/admin/rss/edit_export' )}:</label>
         <select name="Item_Class_Attribute_Title_{$Source:index}" title="{'Use this drop-down to select the attribute that should be exported as the title of the RSS export entry.'|i18n('design/admin/rss/edit_export')}">
         {section name=ClassAttribute loop=$rss_export.item_list[$Source:index].class_attributes}
         <option value="{$:item.identifier}"
             {section name=ShowSelected show=eq( $Source:item.title, $:item.identifier )}
                 selected="selected"
             {/section}>{$:item.name|wash}</option>
         {/section}
         </select>
         <b>Use Template:</b>&nbsp;<input type="checkbox" name="Item_Title_Use_Template_{$Source:index}" {section show=$rss_export.item_list[$Source:index].use_template.title}checked="checked"{/section}/>
       </div>

       <div class="block">
         <label>{'Description'|i18n( 'design/admin/rss/edit_export' )}:</label>
         <select name="Item_Class_Attribute_Description_{$Source:index}" title="{'Use this drop-down to select the attribute that should be exported as the description of the RSS export entry.'|i18n('design/admin/rss/edit_export')}" >
         {section name=ClassAttribute loop=$rss_export.item_list[$Source:index].class_attributes}
         <option value="{$:item.identifier|wash}"
             {section name=ShowSelected show=eq( $Source:item.description, $:item.identifier )}
                 selected="selected"
             {/section}>{$:item.name|wash}</option>
         {/section}
         </select>
         <b>Use Template:</b>&nbsp;<input type="checkbox" name="Item_Description_Use_Template_{$Source:index}" {section show=$rss_export.item_list[$Source:index].use_template.description}checked="checked"{/section}/>
       </div>

       <div class="block">
         <label>{'Category'|i18n( 'design/admin/rss/edit_export' )} ({'optional'|i18n( 'design/admin/rss/edit_export' )}):</label>
         <select name="Item_Class_Attribute_Category_{$Source:index}" title="{'Use this drop-down to select the attribute that should be exported as the category of the RSS export entry.'|i18n('design/admin/rss/edit_export')}" >
         <option value="">[{'Skip'|i18n('design/admin/rss/edit_export')}]</option>
         {section name=ClassAttribute loop=$rss_export.item_list[$Source:index].class_attributes}
         <option value="{$:item.identifier|wash}"
             {section name=ShowSelected show=eq( $Source:item.category, $:item.identifier )}
                 selected="selected"
             {/section}>{$:item.name|wash}</option>
         {/section}
         </select>
       </div>
       {/section}

       <input class="button" type="submit" name="{concat( 'RemoveSource_', $Source:index )}" value="{'Remove this source'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click to remove this source from the RSS export.'|i18n('design/admin/rss/edit_export')}" />
<hr />
    {/section}

    <input class="button" type="submit" name="AddSourceButton" value="{'Add source'|i18n( 'design/admin/rss/edit_export' )}" title="{'Click to add a new source to the RSS export.'|i18n('design/admin/rss/edit_export')}" />

</div>

{* DESIGN: Content END *}</div></div></div>

    <div class="controlbar">
{* DESIGN: Control bar START *}<div class="box-bc"><div class="box-ml"><div class="box-mr"><div class="box-tc"><div class="box-bl"><div class="box-br">
    <div class="block">
        <input class="button" type="submit" name="StoreButton" value="{'OK'|i18n( 'design/admin/rss/edit_export' )}" title="{'Apply the changes and return to the RSS overview.'|i18n('design/admin/rss/edit_export')}" />
        <input class="button" type="submit" name="RemoveButton" value="{'Cancel'|i18n( 'design/admin/rss/edit_export' )}" title="{'Cancel the changes and return to the RSS overview.'|i18n('design/admin/rss/edit_export')}" />
    </div>
{* DESIGN: Control bar END *}</div></div></div></div></div></div>
    </div>

</div>
</form>

{literal}
<script language="JavaScript" type="text/javascript">
<!--
    window.onload=function()
    {
        document.getElementById('exportName').select();
        document.getElementById('exportName').focus();
    }
-->
</script>
{/literal}
