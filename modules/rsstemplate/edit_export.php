<?php
//
// Created on: <16-Jan-2010 08:05:08 Socrattes>
//
// SOFTWARE NAME: OPSA RSS Template Extension
// SOFTWARE RELEASE: 1.0
// BUILD VERSION:
// COPYRIGHT NOTICE: Copyright (C) Organizacion Publicitaria S.A.
// SOFTWARE LICENSE: GNU General Public License v2.0
// NOTICE: >
//   This program is free software; you can redistribute it and/or
//   modify it under the terms of version 2.0  of the GNU General
//   Public License as published by the Free Software Foundation.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of version 2.0 of the GNU General
//   Public License along with this program; if not, write to the Free
//   Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
//   MA 02110-1301, USA.
//
//

$Module = $Params['Module'];

require_once( 'kernel/common/template.php' );
$http = eZHTTPTool::instance();

$valid = true;
$validationErrors = array();

if ( isset( $Params['RSSExportID'] ) )
    $RSSExportID = $Params['RSSExportID'];
else
    $RSSExportID = false;

if ( $http->hasPostVariable( 'RSSExport_ID' ) )
    $RSSExportID = $http->postVariable( 'RSSExport_ID' );

if ( $Module->isCurrentAction( 'Store' ) )
{

    $storeResult = opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http, true );

    if ( $storeResult['valid'] && $storeResult['published'] )
        return $Module->redirectTo( '/rsstemplate/list' );
    if ( !$storeResult['valid'] )
    {
        $valid = false;
        $validationErrors = $storeResult['validation_errors'];
    }
}
else if ( $Module->isCurrentAction( 'UpdateItem' ) )
{
    opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http );
}
else if ( $Module->isCurrentAction( 'AddItem' ) )
{
    $rssExportItem = opsaRSSExportItem::create( $RSSExportID );
    $rssExportItem->store();
    opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http );
}
else if ( $Module->isCurrentAction( 'Cancel' ) )
{
    $rssExport = opsaRSSExport::fetch( $RSSExportID, true, opsaRSSExport::STATUS_DRAFT );
    if ( $rssExport )
        $rssExport->removeThis();
    return $Module->redirectTo( '/rsstemplate/list' );
}
else if ( $Module->isCurrentAction( 'BrowseImage' ) )
{
    opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http );
    eZContentBrowse::browse( array( 'action_name' => 'RSSExportImageBrowse',
                                    'description_template' => 'design:rss/browse_image.tpl',
                                    'from_page' => '/rsstemplate/edit_export/'. $RSSExportID .'/0/ImageSource' ),
                             $Module );
}
else if ( $Module->isCurrentAction( 'RemoveImage' ) )
{
    $rssExport = opsaRSSExport::fetch( $RSSExportID, true, opsaRSSExport::STATUS_DRAFT );
    $rssExport->setAttribute( 'image_id', 0 );
    $rssExport->store();
}


if ( $http->hasPostVariable( 'Item_Count' ) )
{

    $db = eZDB::instance();
    $db->begin();
    for ( $itemCount = 0; $itemCount < $http->postVariable( 'Item_Count' ); $itemCount++ )
    {
        if ( $http->hasPostVariable( 'SourceBrowse_'.$itemCount ) )
        {
            opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http );
            eZContentBrowse::browse( array( 'action_name' => 'RSSObjectBrowse',
                                            'description_template' => 'design:rss/browse_source.tpl',
                                            'from_page' => '/rsstemplate/edit_export/'. $RSSExportID .'/'. $http->postVariable( 'Item_ID_'.$itemCount ) .'/NodeSource' ),
                                     $Module );
            break;
        }

        // remove selected source (if any)
        if ( $http->hasPostVariable( 'RemoveSource_'.$itemCount ) )
        {
            $itemID = $http->postVariable( 'Item_ID_'.$itemCount );
            if ( ( $rssExportItem = opsaRSSExportItem::fetch( $itemID, true, opsaRSSExport::STATUS_DRAFT ) ) )
            {
                // remove the draft version
                $rssExportItem->remove();
                // remove the published version
                $rssExportItem->setAttribute( 'status', opsaRSSExport::STATUS_VALID );
                $rssExportItem->remove();
                opsaRSSTemplateEditFunction::storeRSSExport( $Module, $http );
                
                //remove template item
                if ( ( $rssExportItemTpl = opsaRSSExportItemTemplate::fetch( $itemID, true ) ) ){
                	$rssExportItemTpl->remove();
                }
            }

            break;
        }
    }
    $db->commit();
}

if ( is_numeric( $RSSExportID ) )
{
    $rssExportID = $RSSExportID;
    $rssExport = opsaRSSExport::fetch( $RSSExportID, true, opsaRSSExport::STATUS_DRAFT );

    if ( $rssExport )
    {
        $user = eZUser::currentUser();
        $contentIni = eZINI::instance( 'content.ini' );
        $timeOut = $contentIni->variable( 'RSSExportSettings', 'DraftTimeout' );
        if ( $rssExport->attribute( 'modifier_id' ) != $user->attribute( 'contentobject_id' ) &&
             $rssExport->attribute( 'modified' ) + $timeOut > time() )
        {
            // locked editing
            $tpl = templateInit();

            $tpl->setVariable( 'rss_export', $rssExport );
            $tpl->setVariable( 'rss_export_id', $rssExportID );
            $tpl->setVariable( 'lock_timeout', $timeOut );

            $Result = array();
            $Result['content'] = $tpl->fetch( 'design:rss/edit_export_denied.tpl' );
            $Result['path'] = array( array( 'url' => false,
                                            'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );
            return $Result;
        }
        else if ( $timeOut > 0 && $rssExport->attribute( 'modified' ) + $timeOut < time() )
        {
            $rssExport->removeThis();
            $rssExport = false;
        }
    }
    if ( !$rssExport )
    {
        $rssExport = opsaRSSExport::fetch( $RSSExportID, true, opsaRSSExport::STATUS_VALID );
        if ( $rssExport )
        {
            $db = eZDB::instance();
            $db->begin();
            $rssItems = $rssExport->fetchItems();
            $rssExport->setAttribute( 'status', opsaRSSExport::STATUS_DRAFT );
            $rssExport->store();
            foreach( $rssItems as $rssItem )
            {
                $rssItem->setAttribute( 'status', opsaRSSExport::STATUS_DRAFT );
                $rssItem->store();
            }
            $db->commit();
        }
        else
        {
            return $Module->handleError( eZError::KERNEL_NOT_AVAILABLE, 'kernel' );
        }
    }

    switch ( $Params['BrowseType'] )
    {
        case 'NodeSource':
        {
            $nodeIDArray = $http->hasPostVariable( 'SelectedNodeIDArray' ) ? $http->postVariable( 'SelectedNodeIDArray' ) : null;
            if ( isset( $nodeIDArray ) && !$http->hasPostVariable( 'BrowseCancelButton' ) )
            {
                $rssExportItem = opsaRSSExportItem::fetch( $Params['RSSExportItemID'], true, opsaRSSExport::STATUS_DRAFT );
                $rssExportItem->setAttribute( 'source_node_id', $nodeIDArray[0] );
                $rssExportItem->store();
            }
        } break;

        case 'ImageSource':
        {
            $imageNodeIDArray = $http->hasPostVariable( 'SelectedNodeIDArray' ) ? $http->postVariable( 'SelectedNodeIDArray' ) : null;
            if ( isset( $imageNodeIDArray ) && !$http->hasPostVariable( 'BrowseCancelButton' ) )
            {
                $rssExport->setAttribute( 'image_id', $imageNodeIDArray[0] );
            }
        } break;
    }
}
else // New RSSExport
{
    $user = eZUser::currentUser();
    $user_id = $user->attribute( "contentobject_id" );


    $db = eZDB::instance();
    $db->begin();

    // Create default rssExport object to use
    $rssExport = opsaRSSExport::create( $user_id );
    $rssExport->store();
    $rssExportID = $rssExport->attribute( 'id' );

    // Create One empty export item
    $rssExportItem = opsaRSSExportItem::create( $rssExportID );
    $rssExportItem->store();

    $db->commit();
}

$tpl = templateInit();
$config = eZINI::instance( 'site.ini' );
$rssVersionArray = $config->variable( 'RSSSettings', 'AvailableVersionList' );
$rssDefaultVersion = $config->variable( 'RSSSettings', 'DefaultVersion' );
$numberOfObjectsArray = $config->variable( 'RSSSettings', 'NumberOfObjectsList' );
$numberOfObjectsDefault = $config->variable( 'RSSSettings', 'NumberOfObjectsDefault' );

// Get Classes and class attributes
$classArray = eZContentClass::fetchList();

$tpl->setVariable( 'rss_version_array', $rssVersionArray );
$tpl->setVariable( 'rss_version_default', $rssDefaultVersion );
$tpl->setVariable( 'number_of_objects_array', $numberOfObjectsArray );
$tpl->setVariable( 'number_of_objects_default', $numberOfObjectsDefault );

$tpl->setVariable( 'rss_class_array', $classArray );
$tpl->setVariable( 'rss_export', $rssExport );
$tpl->setVariable( 'rss_export_id', $rssExportID );

// BC for old templates
$tpl->setVariable( 'validaton', !$valid );
// New validation handling
$tpl->setVariable( 'valid', $valid );
$tpl->setVariable( 'validation_errors', $validationErrors );

$Result = array();
$Result['content'] = $tpl->fetch( "design:rss/edit_export.tpl" );
$Result['path'] = array( array( 'url' => false,
                                'text' => ezi18n( 'kernel/rss', 'Really Simple Syndication' ) ) );


?>
