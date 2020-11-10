<?php



namespace app\components;

/*
 * Includes
 */
// use yii\base\Widget;
use app\models\Notification;
//use webvimark\modules\UserManagement\models\User as Userw;
use yii\helpers\Html;
use yii\helpers\Url;
use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\web\UploadedFile;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\Software;
use app\models\SoftwareUpload;
use app\models\SoftwareUploadExisting;
use app\models\SoftwareEdit;
use app\models\SoftwareRemove;
use app\models\ImageRequest;
use webvimark\modules\UserManagement\models\User;
use app\models\RunHistory;
use app\models\SoftwareInput;
use app\models\Workflow;
use yii\data\Pagination;


Yii::$app->getView()->registerJsFile('@web/js/components/projectWindow.js', ['depends' => [\yii\web\JqueryAsset::className()]]);


class ProjectWindow
{

    public static function show($link)
    {


        $superadmin=(User::hasRole("Admin", $superAdminAllowed = true)) ? 1 : 0;
        $user=User::getCurrentUser()['username'];
        
        if ($superadmin==1)
        {
            $softUser='admin';
        }
        else
        {
            $softUser=$user;
        }
        /**
         * Get the list of software
         */
        $model=new Software;

        $software=$model::getSoftwareNames($softUser);

        
        $descriptions=Software::getSoftwareDescriptions($softUser);
        $images=Software::getOriginalImages($softUser);
        $projectsDropdownSession=Software::getActiveProjects();


        $remaining_jobs_array=[];
        foreach ($projectsDropdownSession as $project) 
        {
              $project_name=trim(explode('(',$project)[0]);
              $remaining=trim(explode(' ',$project)[1]);
              $remaining_jobs=trim(explode('(', $remaining)[1]);
              $projectsDropdownSession[$project_name]=$project_name;
              $remaining_jobs_array["$project_name"]=$remaining_jobs;
              
        }

        

        if (!empty($projectsDropdownSession))
        {
            $key=array_key_first($projectsDropdownSession);
            $project_selected=(empty($selected_project) || !isset($projectsDropdownSession[$selected_project]) ) ? $projectsDropdownSession[$key] : $selected_project;

            if(!empty($_GET['selected_project']))
            {
                $_SESSION['selected_project']=$_GET['selected_project'];
                $project_selected=$_GET['selected_project'];
            }
            if(!isset($_SESSION['selected_project']) || empty($_SESSION['selected_project'])) 
            { 
                $_SESSION['selected_project']=$project_selected;
                
            }
            
            $project_name=' ';
            $project_name=trim(explode('(',$_SESSION['selected_project'])[0]);
            $dropdownLabel='Working project:';
            //$remaining_jobs=0;
            
        }
        else
        {
            $dropdownLabel='No active projects available.';
            $project_selected='';
            $project_name='';
            /*
             * Kostis says: If the user has no active project, 
             * then set the session variable to empty string, 
             * otherwise you have a problem.
             */
            $_SESSION['selected_project']='';
        }
        
        echo Html::cssFile('@web/css/components/projectWindow.css');
        

        echo  "<div class='project-egci'>";
        echo        "<div class='col-md-6 col-md-offset-3 project-egci-content text-center'>Working Project:</div>";
        echo        "<div class='col-md-1 col-md-offset-2 project-egci-content arrow-right'  style='padding-top:5px;
                     cursor:pointer;' title='Minimize project window'>
                    <i class='fa fa-angle-double-right' aria-hidden='true'></i></div>";
        echo        "<div class='col-md-12 text-center project-egci-content'>";
    

        if (!empty($projectsDropdownSession))
        {
            echo    Html::dropDownList('dropdown', $_SESSION['selected_project'], $projectsDropdownSession, 
            ['class'=>'project-dropdown']); 
        }
        echo "</div>";
            echo "<div class='col-md-12 text-center project-egci-content' style='color: grey'>Remaining Jobs: &nbsp;
            <span class='rem-jobs'></span>";
            
                foreach ($remaining_jobs_array as $project=>$remaining_jobs)
                {
                    if ($project==$project_name)
                    {
                                $job_class="";
                    }
                    else
                    {
                                $job_class="invisible";
                    }
                    echo  "<span id='$project' class='jobs-div $job_class'>";
                    echo $remaining_jobs;
                    echo '</span>';
                }
            
            echo '</div>';
            echo '<div class="col-md-12 text-center project-egci-content">';
            echo Html::a("Create new project in EG-CI", "https://egci-beta.imsi.athenarc.gr/index.php?r=project%2Fnew-request", ['target'=>"_blank"]);
            echo '</div>';
        echo '</div>';

        echo  "<div class='minimized' style='display:none; text-align:left;'>";
        echo        "<span class='col-md-12 arrow-left'   style='cursor:pointer; padding-left:8px; padding-top:3px;' title='Maximize project window'>
                    <i class='fa fa-angle-double-left' aria-hidden='true'></i></span>";
        echo    "</div>";
         
    }


}

