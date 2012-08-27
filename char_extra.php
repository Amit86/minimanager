<?php

// page header, and any additional required libraries
require_once 'header.php';
require_once 'libs/char_lib.php';
require_once 'libs/item_lib.php';
// minimum permission to view page
valid_login($action_permission['read']);

//########################################################################################################################^M
// SHOW CHARACTER EXTRA INV
//########################################################################################################################^M
function char_extra(&$sqlr, &$sqlc, &$sqlw)
{
    global $output, $lang_global, $lang_char,
            $realm_id, $characters_db, $world_db,
            $action_permission, $user_lvl, $user_name,
            $item_datasite;
            
    // this page uses wowhead tooltops
    wowhead_tt();

    require_once 'core/char/char_security.php';

    $result = $sqlc->query('SELECT account, name, race, class, gender, level
                            FROM characters
                            WHERE guid = '.$id.' LIMIT 1');

    if ($sqlc->num_rows($result))
    {
        $char = $sqlc->fetch_assoc($result);

        $owner_acc_id = $sqlc->result($result, 0, 'account');
        $result = $sqlr->query('SELECT `username`, `gmlevel` FROM `account` LEFT JOIN `account_access` ON `account`.`id`=`account_access`.`id` WHERE `account`.`id` = '.$owner_acc_id.' ORDER BY `gmlevel` DESC LIMIT 1');
        $owner_name = $sqlr->result($result, 0, 'username');
        $owner_gmlvl = $sqlr->result($result, 0, 'gmlevel');
        if (empty($owner_gmlvl))
            $owner_gmlvl = 0;

        if (($user_lvl > $owner_gmlvl)||($owner_name === $user_name))
        {
            $output .= '
            <center>
                <div id="tab_content">
                    <h1>'.$lang_char['extra'].'</h1>
                    <br />';
    
            require_once 'core/char/char_header.php';

            //---------------Page Specific Data Starts Here--------------------------

            $output .= '
                    <br /><br />
                    <table class="lined" style="width: 450px;">
                        <tr>
                            <th width="15%">'.$lang_char['icon'].'</th>
                            <th width="15%">'.$lang_char['quantity'].'</th>
                            <th width="70%">'.$lang_char['name'].'</th>
                        </tr>';

            $sqlw = new SQL;
            $sqlw->connect($world_db[$realm_id]['addr'], $world_db[$realm_id]['user'], $world_db[$realm_id]['pass'], $world_db[$realm_id]['name']);
              
            $result = $sqlw->query('SELECT entry, description FROM item_template WHERE BagFamily = 8192');
            while($bag = $sqlw->fetch_assoc($result))
            {
                $result_2 = $sqlc->query('SELECT item, item_template FROM character_inventory WHERE guid = '.$id.' AND item_template = '.$bag['entry'].' ');
                while ($char = $sqlc->fetch_assoc($result_2))
                {
                    $result_3 = $sqlc->query('SELECT CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(`data`, " ", 15), " ", -1) AS UNSIGNED) AS item FROM item_instance WHERE guid = '.$char['item'].' ');
                    $items = $sqlc->fetch_row($result_3);
                    $output .= '
                        <tr valign="center">
                            <td>
                                <a style="padding:2px;" href="'.$item_datasite.$char['item_template'].'" target="_blank">
                                    <img src="'.get_item_icon($char['item_template'], $sqlm).'" alt="'.$char['item_template'].'" class="icon_border_0" />
                                </a>
                            </td>
                            <td>
                                '.$items['0'].'
                            </td>
                            <td>
                                <span onmousemove="toolTip(\''.$bag['description'].'\', \'item_tooltip\')" onmouseout="toolTip()">'.get_item_name($char['item_template'], $sqlw).'</span>
                            </td>
                        </tr>';
                }
            }

            $output .= '
                    </table>';
                  
            unset($bag);
    
            //---------------Page Specific Data Ends Here--------------------------

            $output .= '
                </div>
            </div>
            <br />';

            require_once 'core/char/char_footer.php';

            $output .= '
            <br />
            </center>';

        }
        else
            error($lang_char['no_permission']);
    }
    else
        error($lang_char['no_char_found']);
}
unset($char);

//########################################################################################################################
// MAIN
//########################################################################################################################

// action variable reserved for future use
//$action = (isset($_GET['action'])) ? $_GET['action'] : NULL;

// load language
$lang_char = lang_char();

$output .= '
    <div class="top">
        <h1>'.$lang_char['character'].'</h1>
    </div>';

// we getting links to realm database and character database left behind by header
// header does not need them anymore, might as well reuse the link
char_extra($sqlr, $sqlc, $sqlw);

//unset($action);
unset($action_permission);
unset($lang_char);

require_once 'footer.php';


?>
