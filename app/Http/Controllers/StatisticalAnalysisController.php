<?php

namespace App\Http\Controllers;

use App\Models\RiskType;
use App\Models\RiskVariable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticalAnalysisController extends Controller
{
    /**
     * Page utama — supply risk types + variables + variable groups
     */
    public function index()
    {
        $riskTypes = RiskType::orderBy('name')->get();

        //----------------------------------------------------------------------
        // LOAD VARIABLES (specific per-unit / per-entitas)
        //----------------------------------------------------------------------
        $variables = RiskVariable::query()
            ->select([
                'risk_variables.id as risk_variable_id',
                'risk_variables.variable_name',
                'risk_variables.value_type',
                'risk_variables.time_dimension',
                'risk_variables.unit_value',
                'risk_variables.notes',
                'risks.id as risk_id',
                'risks.name as risk_name',
                'risks.unit_id',
                'risks.entitas_id',
                'units.name as unit_name',
                'entitas.name as entitas_name',
                'risks.risk_type_id',
                DB::raw('COUNT(risk_values.id) as value_count'),
                DB::raw('MAX(risk_values.updated_at) as last_updated'),
                DB::raw('(COUNT(risk_values.id)) as completeness')
            ])
            ->leftJoin('risks', 'risk_variables.risk_id', '=', 'risks.id')
            ->leftJoin('units', 'risks.unit_id', '=', 'units.id')
            ->leftJoin('entitas', 'risks.entitas_id', '=', 'entitas.id')
            ->leftJoin('risk_values', 'risk_variables.id', '=', 'risk_values.risk_variable_id')
            ->groupBy(
                'risk_variables.id',
                'risk_variables.variable_name',
                'risk_variables.value_type',
                'risk_variables.time_dimension',
                'risk_variables.unit_value',
                'risk_variables.notes',
                'risks.id',
                'risks.name',
                'risks.unit_id',
                'risks.entitas_id',
                'units.name',
                'entitas.name',
                'risks.risk_type_id'
            )
            ->get();

        //----------------------------------------------------------------------
        // ATTACH VALUES FOR EACH VARIABLE (needed for Monte Carlo)
        //----------------------------------------------------------------------
        foreach ($variables as $v) {
            $v->values = DB::table('risk_values')
                ->where('risk_variable_id', $v->risk_variable_id)
                ->select(['value', 'year', 'quarter', 'month'])
                ->orderBy('year')
                ->orderBy('month')
                ->orderBy('quarter')
                ->get();
        }

        //----------------------------------------------------------------------
        // GROUP / AGGREGATE VARIABLES
        // Grouping rule:
        //    group by (risk_type_id + variable_name)
        //----------------------------------------------------------------------
        $variable_groups = [];

        foreach ($variables as $v) {
            $key = $v->risk_type_id . '::' . $v->variable_name;

            if (!isset($variable_groups[$key])) {
                $variable_groups[$key] = [
                    'group_id'         => $key,
                    'risk_type_id'     => $v->risk_type_id,
                    'label'            => $v->variable_name,
                    'value_types'      => [],
                    'time_dimensions'  => [],
                    'available_units'  => [],
                    'available_entitas'=> [],
                    'members'          => [],
                ];
            }

            // collect metadata
            if ($v->value_type) {
                $variable_groups[$key]['value_types'][] = $v->value_type;
            }
            if ($v->time_dimension) {
                $variable_groups[$key]['time_dimensions'][] = $v->time_dimension;
            }
            if ($v->unit_name) {
                $variable_groups[$key]['available_units'][] = $v->unit_name;
            }
            if ($v->entitas_name) {
                $variable_groups[$key]['available_entitas'][] = $v->entitas_name;
            }

            // collect member
            $variable_groups[$key]['members'][] = [
                'risk_variable_id' => $v->risk_variable_id,
                'risk_id'          => $v->risk_id,
                'risk_name'        => $v->risk_name,
                'unit_name'        => $v->unit_name,
                'entitas_name'     => $v->entitas_name,
                'value_type'       => $v->value_type,
                'time_dimension'   => $v->time_dimension,
                'values'           => $v->values,
            ];
        }

        // cleanup duplicates
        $variable_groups = array_map(function($g){
            $g['value_types']      = array_values(array_unique($g['value_types']));
            $g['time_dimensions']  = array_values(array_unique($g['time_dimensions']));
            $g['available_units']  = array_values(array_unique($g['available_units']));
            $g['available_entitas']= array_values(array_unique($g['available_entitas']));
            return $g;
        }, $variable_groups);

        //----------------------------------------------------------------------
        // RETURN TO VIEW
        //----------------------------------------------------------------------
        return view('risk.analysis', [
            'riskTypes'        => $riskTypes,
            'variables'        => $variables,
            'variable_groups'  => array_values($variable_groups),
        ]);
    }
}
