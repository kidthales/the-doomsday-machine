Base parameter rationale & trade-offs:

| Parameter        | Creative | Precision | Balanced | Rationale                                                                                            |
|------------------|----------|-----------|----------|------------------------------------------------------------------------------------------------------|
| `temperature`    | 0.85     | 0.1       | 0.5      | Controls output randomness. Higher values increase novelty; lower values enforce determinism.        |
| `top_p`          | 0.95     | 0.8       | 0.9      | Nucleus sampling threshold. Balances diversity vs. coherence.                                        |
| `top_k`          | 50       | 20        | 40       | Limits vocabulary sampling. Lower `k` reduces hallucination in technical tasks.                      |
| `repeat_penalty` | 1.1      | 1.05      | 1.05     | Discourages token repetition. Slightly higher for creative to avoid looping; near 1.0 for precision. |
| `num_ctx`        | 4096     | 8192      | 4096     | Context window size. Precision tasks often require longer reasoning chains or document parsing.      |
