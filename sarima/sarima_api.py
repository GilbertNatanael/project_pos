from flask import Flask, request, jsonify
from flask_cors import CORS
import joblib
import pandas as pd
import numpy as np
from datetime import datetime, timedelta
import os

app = Flask(__name__)
CORS(app)  # Enable CORS for Laravel frontend

# Configuration
MODEL_DIR = './models'  # Update this path
ITEMS = [
    "Besi beton 6 MM ASLI SNI",
    "Besi beton 8 MM ASLI SNI", 
    "PAKU 10 CM(4\")",
    "PAKU 7 CM(3\")",
    "Pipa Galv",
    "SENG GEL KALISCO 0,20",
    "Semen Kupang"
]

def load_model_for_prediction(item_name):
    """Load saved model for making predictions"""
    try:
        clean_item_name = item_name.replace(' ', '_').replace('/', '_').replace('(', '').replace(')', '').replace('"', '')
        model_filename = f"{MODEL_DIR}/{clean_item_name}_sarima_model.pkl"
        model_data = joblib.load(model_filename)
        return model_data
    except Exception as e:
        print(f"Error loading model for {item_name}: {e}")
        return None

def generate_date_range(date_from, date_to):
    """Generate list of dates between date_from and date_to"""
    start_date = datetime.strptime(date_from, '%Y-%m-%d').date()
    end_date = datetime.strptime(date_to, '%Y-%m-%d').date()
    
    dates = []
    current_date = start_date
    while current_date <= end_date:
        dates.append(current_date.strftime('%Y-%m-%d'))
        current_date += timedelta(days=1)
    
    return dates

@app.route('/predict', methods=['POST'])
def predict_sales():
    try:
        data = request.get_json()
        item_name = data.get('item_name')
        date_from = data.get('date_from')
        date_to = data.get('date_to')
        days_ahead = data.get('days_ahead', 7)
        
        if item_name not in ITEMS:
            return jsonify({'error': 'Item not found'}), 400
        
        if not date_from or not date_to:
            return jsonify({'error': 'date_from and date_to are required'}), 400
            
        # Load model
        model_data = load_model_for_prediction(item_name)
        if model_data is None:
            return jsonify({'error': 'Model not found'}), 404
            
        # Make prediction
        fitted_model = model_data['fitted_model']
        
        # Calculate days between dates
        start_date = datetime.strptime(date_from, '%Y-%m-%d').date()
        end_date = datetime.strptime(date_to, '%Y-%m-%d').date()
        days_diff = (end_date - start_date).days + 1
        
        # Generate predictions for the date range
        forecast_result = fitted_model.get_forecast(steps=days_diff)
        predictions = forecast_result.predicted_mean
        confidence_intervals = forecast_result.conf_int()
        
        # Ensure non-negative predictions
        predictions = np.maximum(predictions, 0)
        
        # Generate exact date range
        dates = generate_date_range(date_from, date_to)
        
        # Format response
        response = {
            'item_name': item_name,
            'date_from': date_from,
            'date_to': date_to,
            'predictions': [
                {
                    'date': dates[i],
                    'predicted_quantity': float(predictions.iloc[i]),
                    'lower_bound': float(confidence_intervals.iloc[i, 0]),
                    'upper_bound': float(confidence_intervals.iloc[i, 1])
                }
                for i in range(len(predictions))
            ],
            'model_performance': {
                'mape': float(model_data['mape']),
                'mae': float(model_data['mae']),
                'rmse': float(model_data['rmse'])
            }
        }
        
        return jsonify(response)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

@app.route('/items', methods=['GET'])
def get_items():
    """Get list of available items"""
    return jsonify({'items': ITEMS})

@app.route('/predict/all', methods=['POST'])
def predict_all_items():
    """Predict for all items at once"""
    try:
        data = request.get_json()
        date_from = data.get('date_from')
        date_to = data.get('date_to')
        days_ahead = data.get('days_ahead', 7)
        
        if not date_from or not date_to:
            return jsonify({'error': 'date_from and date_to are required'}), 400
        
        # Calculate days between dates
        start_date = datetime.strptime(date_from, '%Y-%m-%d').date()
        end_date = datetime.strptime(date_to, '%Y-%m-%d').date()
        days_diff = (end_date - start_date).days + 1
        
        # Generate exact date range
        dates = generate_date_range(date_from, date_to)
        
        results = {}
        for item in ITEMS:
            model_data = load_model_for_prediction(item)
            if model_data is not None:
                fitted_model = model_data['fitted_model']
                forecast_result = fitted_model.get_forecast(steps=days_diff)
                predictions = forecast_result.predicted_mean
                predictions = np.maximum(predictions, 0)
                
                results[item] = {
                    'predictions': [
                        {
                            'date': dates[i],
                            'predicted_quantity': float(predictions.iloc[i])
                        }
                        for i in range(len(predictions))
                    ],
                    'mape': float(model_data['mape'])
                }
        
        return jsonify(results)
        
    except Exception as e:
        return jsonify({'error': str(e)}), 500

if __name__ == '__main__':
    app.run(host='0.0.0.0', port=5000, debug=True)