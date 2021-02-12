<?php
/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
namespace app\controllers;

use app\models\TicketBody;
use app\models\TicketFile;
use app\models\TicketHead;
use app\models\TicketUploadForm;
use yii\filters\AccessControl;
use yii\filters\AccessRule;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\UploadedFile;
use app\models\Notification;
use app\models\TicketConfig;
use app\models\User;

/**
 * Default controller for the `ticket` module
 */
class TicketUserController extends Controller
{
    public $module;

    // public function beforeAction($action)
    // {
    //     TicketConfig::=new TicketConfig;
    //     return parent::beforeAction($action);
    // }

    public function behaviors()
    {
        return [
            'access' => [
                'class'      => AccessControl::className(),
                'ruleConfig' => [
                    'class' => AccessRule::className(),
                ],
                'rules'      => [
                    [
                        'actions' => ['index', 'view', 'open'],
                        'allow'   => true,
                        'roles'   => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Делается выборка тела тикета по id и отображаем данные
     * Если пришел пустой результат показываем список тикетов
     * Создаем экземпляр новой модели тикета
     * К нам пришел пост делаем загрузку в модель и проходим валидацию, если все хорошо делаем выборку шапки, меняем ей статус и сохраняем
     * Записываем id тикета новому ответу чтоб не потерялся и сохроняем новый ответ
     *
     * @param $id
     * @return string
     */
    public function actionView($id)
    {
        // TicketConfig::= new TicketConfig;

        $ticket = TicketHead::findOne([
            'id'      => $id,
            'user_id' => \Yii::$app->user->id,
        ]);
        if ($ticket && $ticket->status == TicketHead::ANSWER) {
            $ticket->status = TicketHead::VIEWED;
            $ticket->save();
        }

        $thisTicket = TicketBody::find()->where(['id_head' => $id])->joinWith('file')->orderBy('date DESC')->all();

        if (!$ticket || !$thisTicket) {
            return $this->actionIndex();
        }

        $newTicket = new TicketBody();
        $ticketFile = new TicketFile();

        if (\Yii::$app->request->post() && $newTicket->load(\Yii::$app->request->post()) && $newTicket->validate()) {

            $ticket->status = TicketHead::WAIT;

            $uploadForm = new TicketUploadForm();
            $uploadForm->imageFiles = UploadedFile::getInstances($ticketFile, 'fileName');

            if ($ticket->save() && $uploadForm->upload()) {
                $newTicket->id_head = $id;
                $newTicket->save();

                TicketFile::saveImage($newTicket, $uploadForm);
            } else {
                \Yii::$app->session->setFlash('error', $uploadForm->firstErrors['imageFiles']);

                return $this->render('view', [
                    'thisTicket' => $thisTicket,
                    'newTicket'  => $newTicket,
                    'fileTicket' => $ticketFile,
                ]);
            }

            if (\Yii::$app->request->isAjax) {
                return 'OK';
            }

            $username=explode('@',$newTicket->name_user)[0];
            $message="User <strong>$username</strong> posted an answer for ticket <strong>$newTicket->text</strong>.";
            $url=Url::to(['/ticket-admin/answer','id'=>$id]);
            foreach (User::getAdminIds() as $admin)
            {
                Notification::notify($admin, $message, '0' ,$url);
            }
            $this->redirect(Url::to(['/ticket-user/index']));
        }

        return $this->render('view', [
            'thisTicket' => $thisTicket,
            'newTicket'  => $newTicket,
            'fileTicket' => $ticketFile,
        ]);
    }

    /**
     * Renders the index view for the module
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = (new TicketHead())->dataProviderUser();
        Url::remember();

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Создаем два экземпляра
     * 1. Шапка тикета
     * 2. Тело тикета
     * Делаем рендеринг страницы
     * Если post, проводим загрузку данных в модели, делаем валидацию
     * Сохраняем сначало шапку, узнаем его id, этот id присваеваем телу сообщения чтоб не потерялось и сохраняем
     *
     * @return string|\yii\web\Response
     */
    public function actionOpen($link='')
    {
        $ticketHead = new TicketHead();
        $ticketBody = new TicketBody();
        $ticketFile = new TicketFile();

        if (\Yii::$app->request->post()) {
            $ticketHead->load(\Yii::$app->request->post());
            $ticketBody->load(\Yii::$app->request->post());

            if ($ticketBody->validate() && $ticketHead->validate()) {
                $ticketHead->page=urldecode($link);
                if ($ticketHead->save()) {
                    $ticketBody->id_head = $ticketHead->getPrimaryKey();
                    $ticketBody->save();

                    $uploadForm = new TicketUploadForm();
                    $uploadForm->imageFiles = UploadedFile::getInstances($ticketFile, 'fileName');
                    if ($uploadForm->upload()) {
                        TicketFile::saveImage($ticketBody, $uploadForm);
                    }

                    if (\Yii::$app->request->isAjax) {
                        return 'OK';
                    }

                    $username=explode('@',$ticketBody->name_user)[0];
                    $message="User <strong>$username</strong> created a new ticket: <strong>$ticketBody->text</strong>.";
                    $url=Url::to(['/ticket-admin/answer','id'=>$ticketHead->id]);
                    foreach (User::getAdminIds() as $admin)
                    {
                        Notification::notify($admin, $message, '0' ,$url);
                    }
                    return $this->redirect(Url::previous());
                }
            }
        }

        return $this->render('open', [
            'ticketHead' => $ticketHead,
            'ticketBody' => $ticketBody,
            'qq'         => TicketConfig::qq,
            'fileTicket' => $ticketFile,
        ]);
    }
}
