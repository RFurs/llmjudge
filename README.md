# Moodle LLM-as-a-Judge Question Bank Plugin

Plugin for **Moodle 4.5+** that evaluates question quality using AI and stores structured evaluation results directly in the Moodle question bank.

The plugin integrates with the **Moodle AI subsystem** and provides automated quality assessment based on predefined criteria such as clarity, cognitive level and others.

The plugin supports evaluation of questions of following types
- multichoice
- short answer
- essay

---

# Table of Contents

- [Lietuviška dokumentacija](#lietuviška-dokumentacija)
  - [Apie plėtinį](#apie-plėtinį)
  - [Plėtinio veikimas](#plėtinio-veikimas)
  - [Vertinimo kriterijai](#vertinimo-kriterijai)
  - [JSON struktūra](#json-struktūra)
  - [Rezultatų peržiūra](#rezultatų-peržiūra)
  - [Plėtinio instaliavimas](#plėtinio-instaliavimas)
  - [DI konfiguravimas](#di-konfiguravimas)

- [English documentation](#english-documentation)
  - [About plugin](#about-plugin)
  - [How the plugin works](#how-the-plugin-works)
  - [Evaluation criteria](#evaluation-criteria)
  - [JSON structure](#json-structure)
  - [Viewing results](#viewing-results)
  - [Plugin installation](#plugin-installation)
  - [AI configuration](#ai-configuration)
  - [Notes](#notes)

---

# Lietuviška dokumentacija

## Apie plėtinį

Šis plėtinys leidžia automatiškai įvertinti **Moodle klausimų kokybę** naudojant dirbtinį intelektą.

Vertinimas atliekamas pagal kelis kriterijus, o rezultatai:

- išsaugomi duomenų bazėje
- pateikiami klausimų banke
- gali būti peržiūrimi detaliai

Plėtinys veikia kaip **Question Bank papildinys**, kuris prideda naują stulpelį su įvertinimu.

**Svarbu:** plėtinys veikia tik su **Moodle 4.5+**.

---

## Plėtinio veikimas

1. Vartotojas pasirenka vieną ar kelis klausimus klausimų banke  
2. Paspaudžia **Evaluate (LLM Judge)** veiksmą  
3. Sugeneruojama užklausa, kuri siunčiama į Moodle DI posistemę  
4. DI grąžina atsakymą **JSON formatu**  
5. Plėtinys:
   - validuoja JSON  
   - apskaičiuoja bendrą įvertinimą  
   - išsaugo rezultatą DB  

---

## Vertinimo kriterijai

Kiekvienas klausimas vertinamas pagal šiuos kriterijus:

- **Aiškumas**
- **Glaustumas**
- **Patikimumas**
- **Korektiškumas**
- **Kognityvinis lygis**
- **Diskriminacija**

Daugiau apie kriterijus galima sužinoti [čia](https://rdegiovanni.github.io/publications/files/SAC2026_validators.pdf)

Kiekvienas kriterijus turi:

- `evaluation` (0 arba 1)
- `feedback`
- `suggestion`

---

## JSON struktūra

DI grąžina tokio formato JSON:
```
{
  "evaluations": [
    {
      "question_id": <int>,
      "question_type": "<string>",
      "criteria": {
        "clarity": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>"
        },
        "conciseness": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>"
        },
        "reliability": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>"
        },
        "correctness": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>"
        },
        "cognitive_level": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>",
          "intended_level": "<string>"
        },
        "discrimination": {
          "score": <0 | 1>,
          "feedback": "<trumpas paaiškinimas>",
          "suggestion": "<patobulinimo pasiūlymas>"
        }
      }
    }
  ]
}
```
---

## Rezultatų peržiūra

Plėtinys prideda naują stulpelį klausimų banke:

- rodomas **bendras įvertinimas**
- paspaudus jį atidaromas puslapis su įvertinimo detalėmis

Puslapyje pateikiama:

- įvertinimo ID
- modelis (pvz., gpt-5.1)
- laikas
- kiekvieno kriterijaus įvertinimas
- grįžtamasis ryšis ir pasiūlymai

---

## Plėtinio instaliavimas

Nukopijuokite plėtinį į: server/moodle/question/bank/llmjudge

Atnaujinus Moodle puslapį, automatiškai bus paleistas diegimas.

---

## DI konfiguravimas

Atidarykite: Administravimas → DI → DI teikėjai

Reikia:

- įjungti tiekėją
- nustatyti API raktą
- įjungti veiksmą ,,Generuoti tekstą``

---

# English documentation

## About plugin

This plugin evaluates **Moodle question quality** using AI.

It integrates into the **Question Bank** and adds:

- automated evaluation  
- persistent storage of results  
- UI for viewing detailed feedback  

**Important:** works on **Moodle 4.5+ only**.

---

## How the plugin works

1. User selects questions in the question bank  
2. Selects "Evaluate with AI"  
3. Plugin builds a prompt and sends it to the AI subsystem  
4. AI returns structured **JSON**  
5. Plugin:
   - validates JSON  
   - calculates overall score  
   - stores results in DB  

---

## Evaluation criteria

Each question is evaluated using:

- **Clarity**
- **Conciseness**
- **Reliability**
- **Correctness**
- **Cognitive level**
- **Discrimination (MCQ only)**

The criteria used in this plugin are based on [this article](https://rdegiovanni.github.io/publications/files/SAC2026_validators.pdf)

Each criterion includes:

- `evaluation` (binary: 0 or 1)  
- `feedback`  
- `suggestion`  

---

## JSON structure
Example:
```
{
  "evaluations": [
    {
      "question_id": <int>,
      "question_type": "<string>",
      "criteria": {
        "clarity": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>"
        },
        "conciseness": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>"
        },
        "reliability": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>"
        },
        "correctness": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>"
        },
        "cognitive_level": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>",
          "intended_level": "<string>"
        },
        "discrimination": {
          "score": <0 | 1>,
          "feedback": "<short explanation>",
          "suggestion": "<improvement suggestion>"
        }
      }
    }
  ]
}
```
---

## Viewing results

The plugin adds a **new column** in the question bank:

- shows **overall score**
- clickable → opens detailed view

Detailed view includes:

- evaluation ID  
- AI model used  
- timestamp  
- per-criterion scores  
- feedback and suggestions  

---

## Plugin installation

Copy and paste the plugin to server/moodle/question/bank/llmjudge

Then open Moodle to trigger installation.

---

## AI configuration

Configure:

- provider  
- API key  
- "generate text" action

---
