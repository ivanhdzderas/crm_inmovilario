<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use App\Models\CalendarActivityModel;
use App\Models\ClientModel;
use App\Models\RealEstateModel;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = session()->get('user_id');
        $data = ['upcomingActivities' => []];
        
        if ($userId) {
            $activityModel = new CalendarActivityModel();
            $data['upcomingActivities'] = $activityModel->getUpcomingActivities($userId, 5);
            
            // Agregar estadísticas inmobiliarias
            $realEstateModel = new RealEstateModel();
            
            // Estadísticas del mes actual para el usuario
            $data['monthlyStats'] = $realEstateModel->getMonthlyStats(date('Y'), date('m'), $userId);
            
            // Top asesores del mes (solo para admin)
            if (session()->get('role_id') == 1) {
                $data['topAgents'] = $realEstateModel->getTopAgentsThisMonth();
                $data['salesByMonth'] = $realEstateModel->getSalesStatsByMonth();
                
                // Estadísticas generales (todos los usuarios)
                $data['generalStats'] = $realEstateModel->getMonthlyStats(date('Y'), date('m'));
            }
        }
        
        return view('dashboard_view', $data);
    }
}