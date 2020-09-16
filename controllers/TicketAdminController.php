<?php

namespace app\controllers;

// use ricco\ticket\Mailer;
use app\models\TicketBody;
use app\models\TicketHead;
use app\models\User;
// use app\models\Module;
use Yii;
use yii\helpers\Url;
use yii\web\Controller;
use app\models\Notification;
use app\models\TicketConfig;
use webvimark\modules\UserManagement\models\User as Userw;

/**
 * @property Module $module
 */
class TicketAdminController extends Controller
{
    public $module;


    // public function beforeAction($action)
    // {
    //     $this->module=new TicketConfig;
    //     return parent::beforeAction($action);
    // }

    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => \yii\filters\AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'matchCallback' => function ($rule, $action) {
                            if (!in_array(Yii::$app->user->getId(), User::getAdminIds())) {
                                return false;
                            }

                            return true;
                        }
                    ],
                ],

            ],
        ];
    }

    /**
     * Выдорка всех тикетов
     * Сортировка по полю дата в обратном порядке
     * Постраничная навигация по 20 тикетов на страницу
     *
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = (new TicketHead())->dataProviderAdmin();
        Url::remember();

        return $this->render('index', ['dataProvider' => $dataProvider]);
    }

    /**
     * Функция вытаскивает данные тикета по id и отображает данные
     * После получения пост данных id тикущего тикета присваевается к ответу и сохраняется
     * Потом идет выборка данных по шапке тикета, меняем ему статус и сохраняем
     * Проверяем если $mailSendAnswer === true значит делаем отправку уведомления од ответе пользователю создавшему тикет
     *
     * @param $id int
     * @return string|\yii\web\Response
     */
    public function actionAnswer($id)
    {
        $thisTicket = TicketBody::find()->where(['id_head' => $id])->joinWith('file')->asArray()->orderBy('date DESC')->all();
        $newTicket = new TicketBody();
        $ticketHead = TicketHead::find()->where(['id'=> $id])->one();

        if (\Yii::$app->request->post()) {
            $newTicket->load(\Yii::$app->request->post());
            $newTicket->id_head = $id;

            if ($newTicket->save()) {
                $ticketHead = TicketHead::findOne($newTicket->id_head);
                $ticketHead->status = TicketHead::ANSWER;

                if ($ticketHead->save()) {

                    $username=explode('@',$newTicket->name_user)[0];
                    $message="Administrator <strong>$username</strong> posted an answer for ticket <strong>$newTicket->text</strong>.";
                    $url=Url::to(['/ticket-admin/view','id'=>$id]);
                    $currentUser=Yii::$app->user->identity->id;

                    /*
                     * Get all users participating in the conversation
                     */
                    $query=new yii\db\Query;
                    $ids=$query->select('u.id')
                               ->from('ticket_body as t')
                               ->innerJoin('user as u',"u.username=t.name_user")
                               ->where(['t.id_head'=>$newTicket->id_head])
                               ->andWhere(['<>','u.id',$currentUser])
                               ->all();
                    
                    foreach ($ids as $id)
                    {
                        Notification::notify($id['id'], $message, Notification::NORMAL ,$url);
                    }

                    return $this->redirect(Url::to(['/ticket-admin/index']));
                }
            }
        }

        return $this->render('answer', ['thisTicket' => $thisTicket, 'newTicket' => $newTicket, 'ticketHead'=>$ticketHead]);
    }

    /**
     * Делаем выборку шапки тикета, меняем ему статус на закрытый и сохоаняем
     * Если $mailSendClosed === true, делаем отправку уведомления на email о закрытии тикета
     *
     * @param $id int id
     * @return \yii\web\Response
     */
    public function actionClosed($id)
    {
        $model = TicketHead::findOne($id);

        $model->status = TicketHead::CLOSED;

        $model->save();

        if (TicketConfig::mailSend !== false) {
            (new Mailer())
                ->sendMailDataTicket($model->topic, $model->status, $model->id, '')
                ->setDataFrom(Yii::$app->params['adminEmail'], TicketConfig::subjectAnswer)
                ->senda('closed');
        }

        return $this->redirect(Url::previous());
    }

    /**
     * Re-open ticket
     *
     * @param $id int id
     * @return \yii\web\Response
     */
    public function actionReopen($id)
    {
        $model = TicketHead::findOne($id);

        $model->status = TicketHead::OPEN;

        $model->save();

        if (TicketConfig::mailSend !== false) {
            (new Mailer())
                ->sendMailDataTicket($model->topic, $model->status, $model->id, '')
                ->setDataFrom(Yii::$app->params['adminEmail'], TicketConfig::subjectAnswer)
                ->senda('closed');
        }

        return $this->redirect(Url::previous());
    }

    /**
     * @param $id int
     * @return \yii\web\Response
     * @throws \Exception
     */
    public function actionDelete($id)
    {
        TicketHead::findOne($id)->delete();

        return $this->redirect(Url::to(['/ticket-admin/index']));
    }

    public function actionOpen()
    {
        $ticketHead = new TicketHead();
        $ticketBody = new TicketBody();

        $userModel = new User;

        $users = $userModel::find()->select(['username as value', 'username as label', 'id as id'])->asArray()->all();
        // print_r($users);
        // exit(0);

        if ($post = \Yii::$app->request->post()) {

            $ticketHead->load($post);
            $ticketBody->load($post);

            if ($ticketHead->validate() && $ticketBody->validate()) {

                $ticketHead->user = $post['TicketHead']['user_id'];
                $ticketHead->status = TicketHead::ANSWER;
                if ($ticketHead->save()) {
                    $ticketBody->id_head = $ticketHead->primaryKey;
                    $ticketBody->save();

                    $this->redirect(Url::previous());
                }
            }
        }

        return $this->render('open', [
            'ticketHead' => $ticketHead,
            'ticketBody' => $ticketBody,
            'qq'         => TicketConfig::qq,
            'users'      => $users,
        ]);
    }
}