<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use PhpParser\Node\Stmt\Switch_;

class Data extends Model
{
    const MAP_FIELDS = [
        "date" => "Дата подписания ГПО ВТС",
        "city" => "Город",
        "sale_center" => "Центр продаж",
        "gender" => "Пол контрагента-страхователя",
        "age" => "Возраст",
        "department" => "Подразделение",
        "age_category" => "Категория возраста",
        "insurance_class" => "КБМ (наименьший)",
        "bonus" => "Бонус",
        "gift" => "Сувенир",
        "sale_channel" => "Канал \nпродаж",
        "agent" => "Посредник",
        "source" => "Источник",
        "new" => "Новый",
        "active" => "Действующий",
        "returned" => "Вернувшийся",
        "cabinet" => "Кабинет на сайте",
        "telemarketing" => "Телемаркетинг",
        "referrer" => "Канал привлечения",
        "ogpo_vts_count" => "ГПО ВТС",
        "medical_count" => "Медицина (Все из узла ДМС)",
        "megapolis_count" => "Мегаполис (Мегаполис, Мегаполис 100,  Страхование имущества)",
        "amortization_count" => "Амортизация",
        "kasko_count" => "Каско (Автокаско, Классик, Прогресс)",
        "kommesk_comfort_count" => "Коммеск-Комфорт",
        "tour_count" => "ВЗР (все из узла страхование путеш-в)",

        "ogpo_vts_result" => "Премия \nОС ГПО ВТС",

        "vts_cross_result" => "Премии др.продукты (Кросс+ доброволки)",
        "vts_overall_sum" => "Сумма премий \n(столбец 30-31)",
        "avg_sum" => "Ср. чек общий (ГПО ВТС, кросс, доброволки)",
        "avg_cross_result" => "Ср.чек кросс и доброволки",
        "overall_lost_count" => "Кол-во\nУбытков общее",

        "vts_lost_count" => "Кол-во\nУбытков по ОС ГПО ВТС",

        "declared_claims" => "Заявленные Претензии\n(статус - Оформление)",

        "pending_claims" => "Рассмотрение \n(Статусы -\nрассм-ся, на подписи)",

        "accepted_claims" => "Статусы - Подписан, Урегулировано",
        "payout_reject_claims" => "Статус - \nОтказ в возмещении",
        "client_reject_claims" => "Статус - \nОтказ заявителя",
        "payout_sum" => "Сумма выплаты\n(по столбцу 39)",
        "isn" => "ISN договора ",
        "client_isn" => "ISN страхователя",
        "vehicle_brand" => "Марка ТС",
        "vehicle_model" => "Модель ТС",
        "vehicle_year" => "Год выпуска ТС",
        "vehicle_year_category" => "Категория года выпуска ТС"
    ];

    const period_categories = [
        "last_week", "last_month", "last_year", "free"
    ];

    public static function getPeriod($period_category){

        switch ($period_category){
            case "last_week":
                return self::getCurrentWeekRange();

            case "last_month":

                break;
        }

    }

    private static function getCurrentWeekRange(){
        $d = strtotime("today");
        $start_week = strtotime("last monday midnight", $d);
        $end_week = strtotime("next sunday", $d);
        $start = date("Y-m-d", $start_week);
        $end = date("Y-m-d", $end_week);

        return [$start, $end];
    }
}
