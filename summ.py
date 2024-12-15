from flask import Flask, render_template, request, jsonify
import os
from mistralai import Mistral
from dotenv import load_dotenv

# Muat variabel lingkungan
load_dotenv()

app = Flask(__name__)

# Inisialisasi API Key Mistral
API_KEY = "YHWHJycK5Lyelo3TSrHWY0em89ja9cEx"
if not API_KEY:
    raise ValueError("API Key for Mistral is missing. Please set it in the .env file.")

client = Mistral(api_key=API_KEY)

# ID agen Anda (ganti sesuai dengan agen yang Anda buat di Mistral)
AGENT_ID = "ag:ffcab88e:20241213:summarize:00ac0c60"

@app.route("/", methods=["GET", "POST"])
def summ():
    summary = ""
    if request.method == "POST":
        # Ambil teks dari form
        input_text = request.form.get("input_text", "")
        if input_text.strip():
            try:
                # Kirim teks ke Mistral Agent untuk dirangkum
                response = client.agents.complete(
                    agent_id=AGENT_ID,
                    messages=[
                        {"role": "user", "content": f"Buatkan rangkuman dari text ini: {input_text}"}
                    ],
                )
                # Ambil hasil ringkasan
                summary = response.choices[0].message.content
            except Exception as e:
                summary = f"Error: {str(e)}"
    return render_template("summ.html", summary=summary)

if __name__ == "__main__":
    app.run(debug=True)
