from flask import Flask, request, jsonify, render_template
from mistralai import Mistral
import os

app = Flask(__name__)

# Konfigurasi API Key
MISTRAL_API_KEY = "YHWHJycK5Lyelo3TSrHWY0em89ja9cEx"
client = Mistral(api_key=MISTRAL_API_KEY)

# Endpoint Chatbot
@app.route('/chat', methods=['POST'])
def chat():
    user_message = request.json.get('message')  # Ambil pesan dari pengguna
    agent_id = "ag:ffcab88e:20241214:untitled-agent:842bb142"  # Agent ID Anda

    try:
        # Kirim pesan ke agen Mistral
        chat_response = client.agents.complete(
            agent_id=agent_id,
            messages=[
                {
                    "role": "user",
                    "content": user_message,
                },
            ],
        )
        # Ambil respon dari Mistral
        bot_reply = chat_response.choices[0].message.content
        return jsonify({"reply": bot_reply})

    except Exception as e:
        return jsonify({"error": str(e)}), 500

# Halaman utama
@app.route('/')
def index():
    return render_template('chatbot.html')

if __name__ == '__main__':
    app.run(debug=True)
