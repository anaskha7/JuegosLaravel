from fastapi import FastAPI, File, HTTPException, UploadFile
from deepface import DeepFace
import os
import shutil
import tempfile


app = FastAPI(title="Servicio facial", version="1.0.0")


def _suffix(filename: str) -> str:
    if "." not in filename:
        return ".jpg"

    extension = filename.rsplit(".", 1)[-1].lower()
    if extension not in {"jpg", "jpeg", "png"}:
        return ".jpg"

    return f".{extension}"


@app.get("/health")
def health():
    return {"status": "ok"}


@app.post("/verify")
async def verify(img1: UploadFile = File(...), img2: UploadFile = File(...)):
    reference_temp = tempfile.NamedTemporaryFile(delete=False, suffix=_suffix(img1.filename))
    capture_temp = tempfile.NamedTemporaryFile(delete=False, suffix=_suffix(img2.filename))

    try:
        with reference_temp as reference_file:
            shutil.copyfileobj(img1.file, reference_file)

        with capture_temp as capture_file:
            shutil.copyfileobj(img2.file, capture_file)

        result = DeepFace.verify(
            img1_path=reference_temp.name,
            img2_path=capture_temp.name,
            detector_backend="opencv",
            model_name="Facenet512",
            enforce_detection=True,
        )

        return {
            "verified": bool(result.get("verified", False)),
            "distance": result.get("distance"),
            "threshold": result.get("threshold"),
            "model": result.get("model"),
        }
    except ValueError as exception:
        raise HTTPException(status_code=422, detail=str(exception))
    except Exception as exception:
        raise HTTPException(status_code=500, detail=f"Error interno del servicio facial: {exception}")
    finally:
        img1.file.close()
        img2.file.close()

        for temp_path in (reference_temp.name, capture_temp.name):
            if os.path.exists(temp_path):
                os.unlink(temp_path)
