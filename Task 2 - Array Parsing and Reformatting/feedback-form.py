from flask import Flask, request, jsonify
from collections import defaultdict

app = Flask(__name__)

def reformat_data(data):
    result = defaultdict(lambda: defaultdict(list))
    
    for item in data:
        if not isinstance(item, dict) or not all(k in item for k in ["category", "sub_category", "id", "name"]):
            raise ValueError("Invalid item: Each item must be a dictionary with 'category', 'sub_category', 'id', and 'name' keys.")
        
        if not isinstance(item["category"], str) or not isinstance(item["sub_category"], str) or not isinstance(item["name"], str):
            raise TypeError("Invalid data type: 'category', 'sub_category', and 'name' must be strings.")
            
        category = item["category"]
        sub_category = item["sub_category"]
        
        result[category][sub_category].append({
            "id": item["id"],
            "name": item["name"]
        })
    
    return {cat: dict(subs) for cat, subs in result.items()}

@app.route("/reformat", methods=["POST"])
def reformat_endpoint():
    if not request.is_json:
        return jsonify({"error": "Request body must be JSON"}), 400
    
    data = request.get_json()
    if not isinstance(data, list):
        return jsonify({"error": "Input must be a list of dictionaries"}), 400

    try:
        formatted = reformat_data(data)
        return jsonify(formatted), 200
    except (ValueError, TypeError) as e:
        return jsonify({"error": str(e)}), 400
    except Exception as e:
        return jsonify({"error": "An unexpected error occurred: " + str(e)}), 500

if __name__ == "__main__":
    app.run(debug=True)