<?php

namespace App\Traits;

trait HasStepWizard
{
    public int $currentStep = 1;

    /**
     * Define the steps in the extending component.
     */
    abstract public function steps(): array;

    /**
     * Get the total number of steps.
     */
    public function maxStep(): int
    {
        return count($this->steps());
    }

    /**
     * Get validation rules for the current step.
     */
    public function getCurrentStepRules(): array
    {
        return $this->steps()[$this->currentStep]['rules'] ?? [];
    }

    /**
     * Get the label for the current step.
     */
    public function getCurrentStepLabel(): string
    {
        return $this->steps()[$this->currentStep]['label'] ?? '';
    }

    /**
     * Get data associated with current step's rules.
     */
    public function getCurrentStepData(): array
    {
        $rules = $this->getCurrentStepRules();
        $data = [];
        foreach (array_keys($rules) as $field) {
            $baseField = explode('.', $field)[0];
            $data[$baseField] = $this->{$baseField} ?? null;
        }
        return $data;
    }

    /**
     * Move to the next step after validating current rules.
     */
    public function nextStep()
    {
        $rules = $this->getCurrentStepRules();
        if (!empty($rules)) {
            $this->validate($rules);
        }

        if ($this->currentStep >= $this->maxStep()) {
            return;
        }

        $this->currentStep++;
    }

    /**
     * Move to the previous step.
     */
    public function previousStep()
    {
        if ($this->currentStep > 1) {
            $this->currentStep--;
        }
    }

    /**
     * Initialize and safeguard step bounds.
     */
    protected function initializeStepWizard()
    {
        $this->currentStep = max(1, min($this->currentStep, $this->maxStep()));
    }
}