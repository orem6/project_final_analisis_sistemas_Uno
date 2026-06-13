<?php

namespace Tests\Unit;

use App\Models\ClinicalRange;
use PHPUnit\Framework\TestCase;

class ClinicalRangeEvaluationTest extends TestCase
{
    private ClinicalRange $temperatureRange;
    private ClinicalRange $oxygenRange;
    private ClinicalRange $glucoseRange;
    private ClinicalRange $heartRateRange;

    protected function setUp(): void
    {
        parent::setUp();

        $this->temperatureRange = new ClinicalRange([
            'record_type' => 'temperature',
            'min_value_normal' => 36.1,
            'max_value_normal' => 37.2,
            'min_value_warning' => 35.0,
            'max_value_warning' => 38.5,
            'critical_low' => 35.0,
            'critical_high' => 38.5,
        ]);

        $this->oxygenRange = new ClinicalRange([
            'record_type' => 'oxygen_saturation',
            'min_value_normal' => 95,
            'max_value_normal' => 100,
            'min_value_warning' => 90,
            'max_value_warning' => 100,
            'critical_low' => 85,
            'critical_high' => null,
        ]);

        $this->glucoseRange = new ClinicalRange([
            'record_type' => 'blood_glucose',
            'min_value_normal' => 70,
            'max_value_normal' => 100,
            'min_value_warning' => 60,
            'max_value_warning' => 126,
            'critical_low' => 60,
            'critical_high' => 126,
        ]);

        $this->heartRateRange = new ClinicalRange([
            'record_type' => 'heart_rate',
            'min_value_normal' => 60,
            'max_value_normal' => 100,
            'min_value_warning' => 50,
            'max_value_warning' => 120,
            'critical_low' => 50,
            'critical_high' => 120,
        ]);
    }

    /** @test */
    public function evaluates_normal_temperature(): void
    {
        $this->assertSame('normal', $this->temperatureRange->evaluate(36.5));
        $this->assertSame('normal', $this->temperatureRange->evaluate(36.1));
        $this->assertSame('normal', $this->temperatureRange->evaluate(37.2));
    }

    /** @test */
    public function evaluates_critical_temperature(): void
    {
        $this->assertSame('critical', $this->temperatureRange->evaluate(34.9));
        $this->assertSame('critical', $this->temperatureRange->evaluate(38.6));
        $this->assertSame('critical', $this->temperatureRange->evaluate(40.0));
    }

    /** @test */
    public function evaluates_warning_high_temperature(): void
    {
        $this->assertSame('warning_high', $this->temperatureRange->evaluate(37.3));
        $this->assertSame('warning_high', $this->temperatureRange->evaluate(38.0));
        $this->assertSame('warning_high', $this->temperatureRange->evaluate(38.4));
    }

    /** @test */
    public function evaluates_warning_low_temperature(): void
    {
        $this->assertSame('warning_low', $this->temperatureRange->evaluate(35.1));
        $this->assertSame('warning_low', $this->temperatureRange->evaluate(35.5));
        $this->assertSame('warning_low', $this->temperatureRange->evaluate(36.0));
    }

    /** @test */
    public function evaluates_normal_oxygen(): void
    {
        $this->assertSame('normal', $this->oxygenRange->evaluate(95));
        $this->assertSame('normal', $this->oxygenRange->evaluate(98));
        $this->assertSame('normal', $this->oxygenRange->evaluate(100));
    }

    /** @test */
    public function evaluates_critical_oxygen(): void
    {
        $this->assertSame('critical', $this->oxygenRange->evaluate(84));
        $this->assertSame('critical', $this->oxygenRange->evaluate(80));
        $this->assertSame('critical', $this->oxygenRange->evaluate(50));
    }

    /** @test */
    public function evaluates_warning_oxygen(): void
    {
        $this->assertSame('warning_low', $this->oxygenRange->evaluate(90));
        $this->assertSame('warning_low', $this->oxygenRange->evaluate(92));
        $this->assertSame('warning_low', $this->oxygenRange->evaluate(94));
    }

    /** @test */
    public function evaluates_normal_glucose(): void
    {
        $this->assertSame('normal', $this->glucoseRange->evaluate(70));
        $this->assertSame('normal', $this->glucoseRange->evaluate(85));
        $this->assertSame('normal', $this->glucoseRange->evaluate(100));
    }

    /** @test */
    public function evaluates_critical_glucose(): void
    {
        $this->assertSame('critical', $this->glucoseRange->evaluate(59));
        $this->assertSame('critical', $this->glucoseRange->evaluate(127));
        $this->assertSame('critical', $this->glucoseRange->evaluate(200));
    }

    /** @test */
    public function evaluates_warning_high_glucose(): void
    {
        $this->assertSame('warning_high', $this->glucoseRange->evaluate(101));
        $this->assertSame('warning_high', $this->glucoseRange->evaluate(110));
        $this->assertSame('warning_high', $this->glucoseRange->evaluate(125));
    }

    /** @test */
    public function evaluates_warning_low_glucose(): void
    {
        $this->assertSame('warning_low', $this->glucoseRange->evaluate(60));
        $this->assertSame('warning_low', $this->glucoseRange->evaluate(65));
        $this->assertSame('warning_low', $this->glucoseRange->evaluate(69));
    }

    /** @test */
    public function evaluates_normal_heart_rate(): void
    {
        $this->assertSame('normal', $this->heartRateRange->evaluate(60));
        $this->assertSame('normal', $this->heartRateRange->evaluate(80));
        $this->assertSame('normal', $this->heartRateRange->evaluate(100));
    }

    /** @test */
    public function evaluates_critical_heart_rate(): void
    {
        $this->assertSame('critical', $this->heartRateRange->evaluate(49));
        $this->assertSame('critical', $this->heartRateRange->evaluate(121));
        $this->assertSame('critical', $this->heartRateRange->evaluate(150));
    }
}
